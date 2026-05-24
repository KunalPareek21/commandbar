<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin so that
 * it is ready for translation.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_i18n
 *
 * Handles loading the plugin text domain for internationalization.
 *
 * @since 1.0.0
 */
class CommandBar_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'commandbar-smart-admin-navigation',
			false,
			dirname( COMMANDBAR_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
