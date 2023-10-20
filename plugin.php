<?php
/**
 * Plugin Template
 *
 * @package     IL Checker
 * @author      Mathieu Lamiot
 * @copyright   Internal Link Checker
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: IL Checker
 * Version:     1.0.0
 * Description: Use IL checker to examine internal links of your homepage to other page of your site
 * Author:      Mathieu Lamiot
 * Text Domain: il-checker
 */

namespace ROCKET_WP_CRAWLER;

define( 'ROCKET_CRWL_PLUGIN_FILENAME', __FILE__ ); // Filename of the plugin, including the file.
define( 'ROCKET_CRWL_IL_CHECKER_RESULT', 'il_checker_result' );
define( 'ROCKET_CRWL_HOMEPAGE_INTERNAL_LINKS', 'il_checker_homepage_internal_links' );

if ( ! defined( 'ABSPATH' ) ) { // If WordPress is not loaded.
	exit( 'WordPress not loaded. Can not load the plugin' );
}

// Load the dependencies installed through composer.
require_once __DIR__ . '/src/plugin.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/support/exceptions.php';
require_once __DIR__ . '/src/classes/helpers.php';

// Plugin initialization.
/**
 * Creates the plugin object on plugins_loaded hook
 *
 * @return void
 */
function wpc_crawler_plugin_init() {
	$wpc_crawler_plugin = new Rocket_Wpc_Plugin_Class();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\wpc_crawler_plugin_init' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_activate' );
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_uninstall' );
