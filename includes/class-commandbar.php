<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * REST API endpoints. It also maintains the unique identifier and current
 * version of the plugin and wires all classes together through the loader.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar
 *
 * Main orchestrator class for the CommandBar plugin. All plugin hooks are
 * registered here via the loader pattern so that hooks are centrally
 * documented and easy to audit.
 *
 * @since 1.0.0
 */
final class CommandBar {

	/**
	 * The loader responsible for maintaining and registering all hooks.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    CommandBar_Loader
	 */
	private CommandBar_Loader $loader;

	/**
	 * Settings instance, shared across admin and other classes.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    CommandBar_Settings
	 */
	private CommandBar_Settings $settings;

	/**
	 * Commands instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    CommandBar_Commands
	 */
	private CommandBar_Commands $commands;

	/**
	 * Initialise the plugin: set up the loader and define all hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->loader   = new CommandBar_Loader();
		$this->settings = new CommandBar_Settings();
		$this->commands = new CommandBar_Commands();

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_rest_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalisation.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale(): void {
		$plugin_i18n = new CommandBar_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all admin-facing hooks.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks(): void {
		$plugin_admin = new CommandBar_Admin( $this->settings, $this->commands );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );

		// Settings registration runs on admin_init.
		$this->loader->add_action( 'admin_init', $this->settings, 'register_settings' );
	}

	/**
	 * Register all REST API hooks.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_rest_hooks(): void {
		$plugin_rest = new CommandBar_Rest_API();
		$this->loader->add_action( 'rest_api_init', $plugin_rest, 'register_routes' );
	}

	/**
	 * Run the loader to execute all registered hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin slug.
	 */
	public function get_plugin_name(): string {
		return 'commandbar';
	}

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin version.
	 */
	public function get_version(): string {
		return COMMANDBAR_VERSION;
	}

	/**
	 * The loader instance.
	 *
	 * @since 1.0.0
	 *
	 * @return CommandBar_Loader
	 */
	public function get_loader(): CommandBar_Loader {
		return $this->loader;
	}
}
