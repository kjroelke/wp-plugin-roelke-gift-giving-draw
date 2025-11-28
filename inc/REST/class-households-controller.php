<?php
/**
 * REST API Households Controller
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\REST;

use KJRoelke\GiftGivingDraw\Persistence\Household_Repository;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API controller for households
 */
class Households_Controller extends WP_REST_Controller {
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
	protected $rest_base = 'households';

	/**
	 * Household repository
	 *
	 * @var Household_Repository
	 */
	private Household_Repository $repository;

	/**
	 * Constructor
	 *
	 * @param Household_Repository $repository Household repository.
	 */
	public function __construct( Household_Repository $repository ) {
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
	 * Get all households
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ): WP_REST_Response {
		$households = $this->repository->get_all();
		$data       = array_map( fn( $h ) => $h->to_array(), $households );
		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get a single household
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$id        = (int) $request->get_param( 'id' );
		$household = $this->repository->get_by_id( $id );

		if ( ! $household ) {
			return new WP_Error( 'not_found', 'Household not found', array( 'status' => 404 ) );
		}

		return new WP_REST_Response( $household->to_array(), 200 );
	}

	/**
	 * Create a household
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$name      = sanitize_text_field( $request->get_param( 'name' ) );
		$household = $this->repository->create( $name );

		if ( ! $household ) {
			return new WP_Error( 'create_failed', 'Failed to create household', array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $household->to_array(), 201 );
	}

	/**
	 * Update a household
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$id   = (int) $request->get_param( 'id' );
		$name = sanitize_text_field( $request->get_param( 'name' ) );

		$household = $this->repository->get_by_id( $id );
		if ( ! $household ) {
			return new WP_Error( 'not_found', 'Household not found', array( 'status' => 404 ) );
		}

		$result = $this->repository->update( $id, $name );
		if ( ! $result ) {
			return new WP_Error( 'update_failed', 'Failed to update household', array( 'status' => 500 ) );
		}

		$updated = $this->repository->get_by_id( $id );
		return new WP_REST_Response( $updated->to_array(), 200 );
	}

	/**
	 * Delete a household
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id        = (int) $request->get_param( 'id' );
		$household = $this->repository->get_by_id( $id );

		if ( ! $household ) {
			return new WP_Error( 'not_found', 'Household not found', array( 'status' => 404 ) );
		}

		$result = $this->repository->delete( $id );
		if ( ! $result ) {
			return new WP_Error( 'delete_failed', 'Failed to delete household', array( 'status' => 500 ) );
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
			'name' => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}
}
