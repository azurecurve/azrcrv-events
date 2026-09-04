<?php

// Check that code was called from ClassicPress with uninstallation constant declared.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Hard-coded rather than referencing the plugin's constants: ClassicPress
// calls uninstall.php standalone, without loading the main plugin file
// first, so those constants are never defined here. These are the literal
// values the plugin has always used (see SETTINGS_OPTION_NAME and
// PLUGIN_POST_TYPE in the main plugin file).
$option_name = 'azrcrv-e';
$post_type   = 'event';

/**
 * Clean up for the current site.
 *
 * Always: deletes the plugin's own settings option.
 *
 * Conditionally, only if the admin opted in via the "Delete event data on
 * uninstall" setting (off by default - see functions-settings.php /
 * tab-widget.php): deletes every event post and its postmeta. The
 * pre-2.0.0 plugin never did this at all, silently leaving event content
 * behind on every uninstall; this makes it an explicit, admin-controlled
 * choice instead.
 */
function azrcrv_e_uninstall_cleanup( $option_name, $post_type ) {

	$settings = get_option( $option_name, array() );
	$delete_data = is_array( $settings ) && ! empty( $settings['general']['delete-data-on-uninstall'] );

	if ( $delete_data ) {
		$event_ids = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'numberposts'    => -1,
				'fields'         => 'ids',
				'suppress_filters' => true,
			)
		);

		foreach ( $event_ids as $event_id ) {
			wp_delete_post( $event_id, true );
		}
	}

	delete_option( $option_name );
}

// Remove from single site.
if ( ! is_multisite() ) {

	azrcrv_e_uninstall_cleanup( $option_name, $post_type );

	// Remove from every site on a multisite network.
} else {
	global $wpdb;

	$site_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_site_id = get_current_blog_id();

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );

		azrcrv_e_uninstall_cleanup( $option_name, $post_type );
	}

	switch_to_blog( $original_site_id );
}
