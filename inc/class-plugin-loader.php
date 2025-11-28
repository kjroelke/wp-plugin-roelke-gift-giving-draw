<?php
/**
 * Plugin Loader
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw;

use KJRoelke\GiftGivingDraw\Admin\Admin_Shortcode;
use KJRoelke\GiftGivingDraw\Persistence\Database_Schema;
use KJRoelke\GiftGivingDraw\Persistence\Drawing_Repository;
use KJRoelke\GiftGivingDraw\Persistence\Household_Repository;
use KJRoelke\GiftGivingDraw\Persistence\Participant_Repository;
use KJRoelke\GiftGivingDraw\REST\Drawings_Controller;
use KJRoelke\GiftGivingDraw\REST\Households_Controller;
use KJRoelke\GiftGivingDraw\REST\Participants_Controller;

/**
 * Initializes the Plugin
 */
class Plugin_Loader {
	/**
	 * The directory path of the plugin
	 *
	 * @var string $dir_path
	 */
	private string $dir_path;

	/**
	 * The directory URL of the plugin
	 *
	 * @var string $dir_url
	 */
	private string $dir_url;

	/**
	 * Years to look back for repeat pairing prevention
	 *
	 * @var int
	 */
	private int $years_lookback = 3;

	/**
	 * Minimum age to be considered adult
	 *
	 * @var int
	 */
	private int $minimum_age = 18;

	/**
	 * Constructor
	 *
	 * @param string $dir_path The directory path of the plugin.
	 */
	public function __construct( string $dir_path ) {
		$this->dir_path = $dir_path;
		$this->dir_url  = plugin_dir_url( $dir_path . '/index.php' );

		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required files
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		// Domain.
		require_once $this->dir_path . 'inc/Domain/class-participant.php';
		require_once $this->dir_path . 'inc/Domain/class-household.php';
		require_once $this->dir_path . 'inc/Domain/class-pairing.php';

		// Persistence.
		require_once $this->dir_path . 'inc/Persistence/class-database-schema.php';
		require_once $this->dir_path . 'inc/Persistence/class-household-repository.php';
		require_once $this->dir_path . 'inc/Persistence/class-participant-repository.php';
		require_once $this->dir_path . 'inc/Persistence/class-drawing-repository.php';

		// REST.
		require_once $this->dir_path . 'inc/REST/class-households-controller.php';
		require_once $this->dir_path . 'inc/REST/class-participants-controller.php';
		require_once $this->dir_path . 'inc/REST/class-drawings-controller.php';

		// Admin.
		require_once $this->dir_path . 'inc/Admin/class-admin-shortcode.php';
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'init', array( $this, 'init_shortcode' ) );
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$household_repository   = new Household_Repository();
		$participant_repository = new Participant_Repository();
		$drawing_repository     = new Drawing_Repository( $participant_repository );

		$households_controller = new Households_Controller( $household_repository );
		$households_controller->register_routes();

		$participants_controller = new Participants_Controller( $participant_repository );
		$participants_controller->register_routes();

		$drawings_controller = new Drawings_Controller(
			$drawing_repository,
			$participant_repository,
		);
		$drawings_controller->register_routes();
	}

	/**
	 * Initialize the admin shortcode
	 *
	 * @return void
	 */
	public function init_shortcode(): void {
		$shortcode = new Admin_Shortcode( $this->dir_path, $this->dir_url );
		$shortcode->init();
	}

	/**
	 * Initializes the Plugin (activation hook)
	 *
	 * @return void
	 */
	public function activate(): void {
		$this->load_dependencies();
		Database_Schema::create_tables();
	}

	/**
	 * Handles Plugin Deactivation
	 * (this is a callback function for the `register_deactivation_hook` function)
	 *
	 * @return void
	 */
	public function deactivate(): void {
		// Leave data intact as per requirements.
	}
}
