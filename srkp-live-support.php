<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/keshavsharma262001-ops/srkp-live-support
 * @since             1.0.0
 * @package           Srkp_Live_Support
 *
 * @wordpress-plugin
 * Plugin Name:       SRKP Live Support
 * Plugin URI:        https://github.com/keshavsharma262001-ops/srkp-live-support
 * Description:       Real-time live chat for WordPress with an admin inbox and Pusher-powered visitor messaging.
 * Version:           1.0.2
 * Author:            SRKP Team
 * Author URI:        https://github.com/keshavsharma262001-ops
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       srkp-live-support
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SRKP_LIVE_SUPPORT_VERSION', '1.0.2' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-srkp-live-support-activator.php
 */
function srkp_live_support_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-srkp-live-support-activator.php';
	Srkp_Live_Support_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-srkp-live-support-deactivator.php
 */
function srkp_live_support_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-srkp-live-support-deactivator.php';
	Srkp_Live_Support_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'srkp_live_support_activate' );
register_deactivation_hook( __FILE__, 'srkp_live_support_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-srkp-live-support.php';
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function srkp_live_support_run() {

	$plugin = new Srkp_Live_Support();
	$plugin->run();

}
srkp_live_support_run();
