<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * WordPress calls this file directly when the plugin is deleted from the
 * Plugins screen (not on deactivation). All plugin-owned data stored in
 * wp_options is removed here.
 *
 * Note: localStorage data (recent commands, dark mode preference, trigger
 * dismiss state) cannot be cleared server-side. Users can clear site data
 * in their browser settings if needed.
 *
 * @package CommandBar
 * @since   1.0.0
 */

// Abort if WordPress uninstall constant is not defined.
// This prevents direct execution of this file.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove all plugin options.
delete_option( 'commandbar_settings' );
delete_option( 'commandbar_version' );

// Remove any search result transients created by the REST API layer.
// These are auto-expiring (60s TTL) but we clean them up proactively.
global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_commandbar_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_commandbar_' ) . '%'
	)
);

// On multisite, clean up options from all sub-sites.
if ( is_multisite() ) {
	$commandbar_site_ids = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
	foreach ( $commandbar_site_ids as $commandbar_site_id ) {
		switch_to_blog( $commandbar_site_id );
		delete_option( 'commandbar_settings' );
		delete_option( 'commandbar_version' );
		restore_current_blog();
	}
}
