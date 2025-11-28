<?php
/**
 * Plugin Name: WordPress Plugin Starter
 * Plugin URI: https://github.com/kjroelke/wp-plugin-starter
 * Description: Quick Boilerplate for starting a new plugin
 * Version: 1.0.0
 * Author: K.J. Roelke
 * Author URI: https://www.kjroelke.online
 * Text Domain: wp-plugin-starter
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.2
 * Requires at least: 6.6.0
 * Tested up to: 6.8.3
 *
 * @package KJRoelke
 * @subpackage PluginStarter
 */

use KJRoelke\Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once __DIR__ . '/inc/class-plugin-loader.php';
$plugin_loader = new Plugin_Loader( plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( $plugin_loader, 'activate' ) );
register_deactivation_hook( __FILE__, array( $plugin_loader, 'deactivate' ) );