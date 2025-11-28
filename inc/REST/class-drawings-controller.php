<?php
/**
 * REST API Drawings Controller
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\REST;

use KJRoelke\GiftGivingDraw\Domain\Pairing_Engine;
use KJRoelke\GiftGivingDraw\Persistence\Drawing_Repository;
use KJRoelke\GiftGivingDraw\Persistence\Participant_Repository;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API controller for drawings
 */
class Drawings_Controller extends WP_REST_Controller {
	/**
	 * Namespace for the REST API
	 *
	 * @var string
	 */
	protected $namespace = 'gift-giving-draw/v1';

	/**
	 * Resource base
	 *
	 * @var string
	 */
	protected $rest_base = 'drawings';

	/**
	 * Drawing repository
	 *
	 * @var Drawing_Repository
	 */
	private Drawing_Repository $drawing_repository;

	/**
	 * Participant repository
	 *
	 * @var Participant_Repository
	 */
	private Participant_Repository $participant_repository;

	/**
	 * Pairing engine
	 *
	 * @var Pairing_Engine
	 */
	private Pairing_Engine $pairing_engine;

	/**
	 * Constructor
	 *
	 * @param Drawing_Repository     $drawing_repository     Drawing repository.
	 * @param Participant_Repository $participant_repository Participant repository.
	 * @param Pairing_Engine         $pairing_engine         Pairing engine.
	 */
	public function __construct(
		Drawing_Repository $drawing_repository,
		Participant_Repository $participant_repository,
		Pairing_Engine $pairing_engine
	) {
		$this->drawing_repository     = $drawing_repository;
		$this->participant_repository = $participant_repository;
		$this->pairing_engine         = $pairing_engine;
	}

	/**
	 * Register routes
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Get all years with drawings.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/years',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_years' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// Get drawing for a specific year.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<year>[\d]{4})',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
			)
		);

		// Generate draft pairings.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/generate',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'generate_draft' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'year' => array(
							'required' => true,
							'type'     => 'integer',
						),
					),
				),
			)
		);

		// Finalize drawing.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/finalize',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'finalize_drawing' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'year'     => array(
							'required' => true,
							'type'     => 'integer',
						),
						'pairings' => array(
							'required' => true,
							'type'     => 'array',
						),
					),
				),
			)
		);

		// Get settings.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get all years with drawings
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_years( $request ): WP_REST_Response {
		$years = $this->drawing_repository->get_years();
		return new WP_REST_Response( $years, 200 );
	}

	/**
	 * Get drawing for a specific year
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$year     = (int) $request->get_param( 'year' );
		$pairings = $this->drawing_repository->get_by_year( $year );

		if ( empty( $pairings ) ) {
			return new WP_Error( 'not_found', 'No drawing found for this year', array( 'status' => 404 ) );
		}

		$data = array_map( fn( $p ) => $p->to_array(), $pairings );
		return new WP_REST_Response(
			array(
				'year'     => $year,
				'pairings' => $data,
			),
			200
		);
	}

	/**
	 * Generate draft pairings (not saved)
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function generate_draft( $request ) {
		$year = (int) $request->get_param( 'year' );

		// Get all participants.
		$participants = $this->participant_repository->get_all();

		if ( empty( $participants ) ) {
			return new WP_Error( 'no_participants', 'No participants found', array( 'status' => 400 ) );
		}

		// Get past pairings.
		$past_pairings = $this->drawing_repository->get_past_pairings(
			$this->pairing_engine->get_years_lookback(),
			$year
		);

		// Generate pairings.
		$pairings = $this->pairing_engine->generate( $participants, $past_pairings, $year );

		if ( null === $pairings ) {
			return new WP_Error(
				'generation_failed',
				'Could not generate valid pairings with the current constraints',
				array( 'status' => 400 )
			);
		}

		$data = array_map( fn( $p ) => $p->to_array(), $pairings );
		return new WP_REST_Response(
			array(
				'year'     => $year,
				'pairings' => $data,
				'is_draft' => true,
			),
			200
		);
	}

	/**
	 * Finalize and save a drawing
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function finalize_drawing( $request ) {
		$year          = (int) $request->get_param( 'year' );
		$pairings_data = $request->get_param( 'pairings' );

		// Reconstruct pairings from data.
		$pairings = array();
		foreach ( $pairings_data as $pairing_data ) {
			$giver    = $this->participant_repository->get_by_id( (int) $pairing_data['giver']['id'] );
			$receiver = $this->participant_repository->get_by_id( (int) $pairing_data['receiver']['id'] );

			if ( ! $giver || ! $receiver ) {
				return new WP_Error( 'invalid_pairing', 'Invalid participant in pairing', array( 'status' => 400 ) );
			}

			$pairings[] = new \KJRoelke\GiftGivingDraw\Domain\Pairing( $giver, $receiver, $year );
		}

		// Save the drawing.
		$result = $this->drawing_repository->save( $pairings, $year );

		if ( ! $result ) {
			return new WP_Error( 'save_failed', 'Failed to save drawing', array( 'status' => 500 ) );
		}

		$data = array_map( fn( $p ) => $p->to_array(), $pairings );
		return new WP_REST_Response(
			array(
				'year'     => $year,
				'pairings' => $data,
				'is_draft' => false,
			),
			201
		);
	}

	/**
	 * Delete drawing for a specific year
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$year = (int) $request->get_param( 'year' );

		if ( ! $this->drawing_repository->year_exists( $year ) ) {
			return new WP_Error( 'not_found', 'No drawing found for this year', array( 'status' => 404 ) );
		}

		$result = $this->drawing_repository->delete_year( $year );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', 'Failed to delete drawing', array( 'status' => 500 ) );
		}

		return new WP_REST_Response( null, 204 );
	}

	/**
	 * Get pairing engine settings
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_settings( $request ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'years_lookback' => $this->pairing_engine->get_years_lookback(),
				'minimum_age'    => $this->pairing_engine->get_minimum_age(),
				'current_year'   => (int) gmdate( 'Y' ),
			),
			200
		);
	}

	/**
	 * Check permissions for getting items
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for getting a single item
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for creating items
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function create_item_permissions_check( $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for deleting items
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function delete_item_permissions_check( $request ): bool {
		return current_user_can( 'manage_options' );
	}
}
