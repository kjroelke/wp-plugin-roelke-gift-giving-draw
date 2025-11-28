<?php
/**
 * Admin Shortcode Handler
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

namespace KJRoelke\GiftGivingDraw\Admin;

/**
 * Handles the admin shortcode for the React application
 */
class Admin_Shortcode {
	/**
	 * Shortcode tag
	 */
	private const SHORTCODE_TAG = 'gift_giving_draw';

	/**
	 * Plugin directory path
	 *
	 * @var string
	 */
	private string $plugin_dir;

	/**
	 * Plugin directory URL
	 *
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * Constructor
	 *
	 * @param string $plugin_dir Plugin directory path.
	 * @param string $plugin_url Plugin directory URL.
	 */
	public function __construct( string $plugin_dir, string $plugin_url ) {
		$this->plugin_dir = $plugin_dir;
		$this->plugin_url = $plugin_url;
	}

	/**
	 * Initialize the shortcode
	 *
	 * @return void
	 */
	public function init(): void {
		add_shortcode( self::SHORTCODE_TAG, array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ): string {
		// Check if user has permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			return '<p>You do not have permission to access this page.</p>';
		}

		// Enqueue scripts and styles.
		$this->enqueue_assets();

		// Return the React app container.
		return '<div id="gift-giving-draw-app"></div>';
	}

	/**
	 * Enqueue scripts and styles for the React app
	 *
	 * @return void
	 */
	private function enqueue_assets(): void {
		$asset_file = $this->plugin_dir . 'build/admin/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'gift-giving-draw-admin',
			$this->plugin_url . 'build/admin/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'gift-giving-draw-admin',
			$this->plugin_url . 'build/admin/index.css',
			array(),
			$asset['version']
		);

		// Localize script with REST API data.
		wp_localize_script(
			'gift-giving-draw-admin',
			'giftGivingDrawConfig',
			array(
				'restUrl'   => esc_url_raw( rest_url( 'gift-giving-draw/v1/' ) ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => $this->plugin_url,
			)
		);
	}
}
