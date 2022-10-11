<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://gov.bc.ca
 * @since             1.0.0
 * @package           WP_Cron_Hook
 *
 * @wordpress-plugin
 * Plugin Name:       WP Cron Hook
 * Plugin URI:        gov.bc.ca
 * Description:       Simple WP cron handler via external crontab and WP REST API.
 * Version:           1.0.0
 * Author:            Spencer
 * Author URI:        gov.bc.ca
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-cron-hook
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
const WP_CRON_HOOK_VERSION = '1.0.0';

// get plugin directory path
$plugin_dir = WP_PLUGIN_DIR . '/wp-cron-hook/';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-basic-forms-activator.php
 */
function activate_WP_Cron_Hook() {
    global $plugin_dir;
	require_once $plugin_dir . 'includes/class-wp-cron-hook-activator.php';
	WP_Cron_Hook_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-basic-forms-deactivator.php
 */
function deactivate_WP_Cron_Hook() {
    global $plugin_dir;
	require_once $plugin_dir . 'includes/class-wp-cron-hook-deactivator.php';
	WP_Cron_Hook_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WP_Cron_Hook' );
register_deactivation_hook( __FILE__, 'deactivate_WP_Cron_Hook' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require $plugin_dir . 'includes/class-wp-cron-hook.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_cron_hook() {

	$plugin = new WP_Cron_Handler();
	$plugin->run();

}
run_wp_cron_hook();
