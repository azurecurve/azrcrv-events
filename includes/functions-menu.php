<?php
/*
	menu functions - admin menu registration. The admin-post save handler
	for the Settings page lives in functions-settings.php, alongside the
	functions it calls.

	Unlike Shortcodes in Comments, Events is reachable both from its own
	custom post type's admin menu (edit.php?post_type=event) and via the
	shared azurecurve cross-plugin menu (registered in
	azurecurve-menu-display.php) - matching the pre-2.0.0 plugin's
	navigation, since the settings page is directly relevant to editors
	working within the Events post type.
*/

/**
 * Declare the Namespace.
 */
namespace azurecurve\Events;

/**
 * Prevent direct access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Add settings link on the Plugins list page.
 */
function add_plugin_action_link( $links, $file ) {

	$this_plugin = PLUGIN_SLUG . '/' . PLUGIN_SLUG . '.php';

	if ( $file === $this_plugin ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=' . PLUGIN_HYPHEN ) ) . '"><img src="' . esc_url( plugins_url( '../assets/images/logo.svg', __FILE__ ) ) . '" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />' . esc_html__( 'Settings', 'azrcrv-e' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Add this plugin's settings page under the Events post type's own admin
 * menu, and under the shared azurecurve cross-plugin menu.
 */
function create_admin_menu() {

	add_submenu_page(
		'edit.php?post_type=' . PLUGIN_POST_TYPE,
		esc_html__( 'Events Settings', 'azrcrv-e' ),
		esc_html__( 'Settings', 'azrcrv-e' ),
		'manage_options',
		PLUGIN_HYPHEN,
		__NAMESPACE__ . '\\display_admin_page'
	);

	add_submenu_page(
		'azrcrv-plugin-menu',
		esc_html__( 'Events Settings', 'azrcrv-e' ),
		esc_html__( 'Events', 'azrcrv-e' ),
		'manage_options',
		PLUGIN_HYPHEN,
		__NAMESPACE__ . '\\display_admin_page'
	);
}
