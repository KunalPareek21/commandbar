<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Activator
 *
 * Handles actions to be performed on plugin activation.
 *
 * @since 1.0.0
 */
class CommandBar_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * Sets default option values so the plugin works immediately with
	 * zero configuration required by the site administrator.
	 *
	 * @since 1.0.0
	 */
	public static function activate(): void {
		$defaults = array(
			'enabled'                => true,
			'show_trigger_button'    => true,
			'trigger_button_position'=> 'bottom-right',
			'show_recent_commands'   => true,
			'recent_commands_count'  => 5,
			'palette_theme'          => 'auto',
			'show_command_icons'     => true,
			'show_shortcut_hints'    => true,
			'enabled_roles'          => array( 'administrator', 'editor', 'author' ),
		);

		// Use add_option so we never overwrite existing settings on re-activation.
		add_option( 'commandbar_settings', $defaults );

		// Store the plugin version for future upgrade routines.
		update_option( 'commandbar_version', COMMANDBAR_VERSION );
	}
}
