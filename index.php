<?php
/**
 * Plugin Name: Gift-Giving Draw
 * Plugin URI: https://github.com/kjroelke/wp-plugin-roelke-gift-giving-draw
 * Description: Generates annual gift-giving pairings with constraints for households and repeat prevention
 * Version: 1.0.0
 * Author: K.J. Roelke
 * Author URI: https://www.kjroelke.online
 * Text Domain: gift-giving-draw
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.2
 * Requires at least: 6.6.0
 * Tested up to: 6.8.3
 *
 * @package KJRoelke
 * @subpackage GiftGivingDraw
 */

use KJRoelke\GiftGivingDraw\Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'GIFT_GIVING_DRAW_VERSION', '1.0.0' );
define( 'GIFT_GIVING_DRAW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIFT_GIVING_DRAW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/inc/class-plugin-loader.php';
$plugin_loader = new Plugin_Loader( plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( $plugin_loader, 'activate' ) );
register_deactivation_hook( __FILE__, array( $plugin_loader, 'deactivate' ) );
