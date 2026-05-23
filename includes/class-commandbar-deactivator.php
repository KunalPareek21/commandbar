<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 * Settings and data are intentionally preserved — they are only removed on uninstall.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Deactivator
 *
 * Handles actions to be performed on plugin deactivation.
 *
 * @since 1.0.0
 */
class CommandBar_Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * Flushes rewrite rules only. All plugin settings and data are preserved
	 * so they are still available if the plugin is reactivated. Data is only
	 * removed via uninstall.php.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
