<?php
/**
 * Plugin Name: CommandBar – Smart Admin Navigation
 * Plugin URI:  https://kunalpareek.in/commandbar
 * Description: A lightweight keyboard-first command palette for WordPress admin. Press CMD+K or CTRL+K anywhere in wp-admin to instantly search posts, pages, users, settings, and run actions without touching the mouse.
 * Version:     1.0.3
 * Author:      Kunal Pareek
 * Author URI:  https://kunalpareek.in
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: commandbar
 * Domain Path: /languages
 * Requires at least: 6.3
 * Requires PHP: 8.0
 * Tested up to: 7.0
 *
 * @package CommandBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version constant.
define( 'COMMANDBAR_VERSION', '1.0.3' );

// Absolute path to the plugin directory (with trailing slash).
define( 'COMMANDBAR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// URL to the plugin directory (with trailing slash).
define( 'COMMANDBAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename for use with activation/deactivation hooks.
define( 'COMMANDBAR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload include files.
 */
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-loader.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-i18n.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-activator.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-deactivator.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-settings.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-commands.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-rest-api.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar-admin.php';
require_once COMMANDBAR_PLUGIN_DIR . 'includes/class-commandbar.php';

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, array( 'CommandBar_Activator', 'activate' ) );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, array( 'CommandBar_Deactivator', 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function commandbar_run() {
	$plugin = new CommandBar();
	$plugin->run();
}
commandbar_run();
