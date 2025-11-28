<?php
/**
 * REST API Participants Controller
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\REST;

use KJRoelke\GiftGivingDraw\Persistence\Participant_Repository;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API controller for participants
 */
class Participants_Controller extends WP_REST_Controller {
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
	protected $rest_base = 'participants';

	/**
	 * Participant repository
	 *
	 * @var Participant_Repository
	 */
	private Participant_Repository $repository;

	/**
	 * Constructor
	 *
	 * @param Participant_Repository $repository Participant repository.
	 */
	public function __construct( Participant_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Register routes
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'household_id' => array(
							'required' => false,
							'type'     => 'integer',
						),
					),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_create_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_create_args(),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get all participants
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$household_id = $request->get_param( 'household_id' );

		if ( $household_id ) {
			$participants = $this->repository->get_by_household( (int) $household_id );
		} else {
			$participants = $this->repository->get_all();
		}

		$data = array_map( fn( $p ) => $p->to_array(), $participants );
		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get a single participant
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$id          = (int) $request->get_param( 'id' );
		$participant = $this->repository->get_by_id( $id );

		if ( ! $participant ) {
			return new WP_Error( 'not_found', 'Participant not found', array( 'status' => 404 ) );
		}

		return new WP_REST_Response( $participant->to_array(), 200 );
	}

	/**
	 * Create a participant
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$household_id = (int) $request->get_param( 'household_id' );
		$name         = sanitize_text_field( $request->get_param( 'name' ) );
		$birth_date   = sanitize_text_field( $request->get_param( 'birth_date' ) );

		// Validate birth_date format.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $birth_date ) ) {
			return new WP_Error( 'invalid_date', 'Birth date must be in Y-m-d format', array( 'status' => 400 ) );
		}

		$participant = $this->repository->create( $household_id, $name, $birth_date );

		if ( ! $participant ) {
			return new WP_Error( 'create_failed', 'Failed to create participant', array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $participant->to_array(), 201 );
	}

	/**
	 * Update a participant
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$id           = (int) $request->get_param( 'id' );
		$household_id = (int) $request->get_param( 'household_id' );
		$name         = sanitize_text_field( $request->get_param( 'name' ) );
		$birth_date   = sanitize_text_field( $request->get_param( 'birth_date' ) );

		$participant = $this->repository->get_by_id( $id );
		if ( ! $participant ) {
			return new WP_Error( 'not_found', 'Participant not found', array( 'status' => 404 ) );
		}

		// Validate birth_date format.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $birth_date ) ) {
			return new WP_Error( 'invalid_date', 'Birth date must be in Y-m-d format', array( 'status' => 400 ) );
		}

		$result = $this->repository->update( $id, $household_id, $name, $birth_date );
		if ( ! $result ) {
			return new WP_Error( 'update_failed', 'Failed to update participant', array( 'status' => 500 ) );
		}

		$updated = $this->repository->get_by_id( $id );
		return new WP_REST_Response( $updated->to_array(), 200 );
	}

	/**
	 * Delete a participant
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id          = (int) $request->get_param( 'id' );
		$participant = $this->repository->get_by_id( $id );

		if ( ! $participant ) {
			return new WP_Error( 'not_found', 'Participant not found', array( 'status' => 404 ) );
		}

		$result = $this->repository->delete( $id );
		if ( ! $result ) {
			return new WP_Error( 'delete_failed', 'Failed to delete participant', array( 'status' => 500 ) );
		}

		return new WP_REST_Response( null, 204 );
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
	 * Check permissions for updating items
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function update_item_permissions_check( $request ): bool {
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

	/**
	 * Get arguments for create/update endpoints
	 *
	 * @return array
	 */
	private function get_create_args(): array {
		return array(
			'household_id' => array(
				'required' => true,
				'type'     => 'integer',
			),
			'name'         => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'birth_date'   => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}
}
