<?php
/*
	script/style enqueue functions.
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

const PLUGIN_VERSION = '2.0.0';

/**
 * Enqueue admin CSS/JS, only on this plugin's own admin page(s) or the
 * shared azurecurve cross-plugin menu page.
 */
function enqueue_admin_assets( $hook ) {

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page identifier, not a state-changing action.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	$on_settings_page = ( PLUGIN_HYPHEN === $page || 'azrcrv-plugin-menu' === $page );

	if ( ! $on_settings_page ) {
		return;
	}

	wp_enqueue_style( PLUGIN_HYPHEN . '-admin-standard', plugins_url( 'assets/css/admin-standard.css', PLUGIN_FILE ), array(), PLUGIN_VERSION );
	wp_enqueue_style( PLUGIN_HYPHEN . '-admin-pluginmenu', plugins_url( 'assets/css/admin-pluginmenu.css', PLUGIN_FILE ), array(), PLUGIN_VERSION );
	wp_enqueue_style( PLUGIN_HYPHEN . '-admin', plugins_url( 'assets/css/admin.css', PLUGIN_FILE ), array( PLUGIN_HYPHEN . '-admin-standard' ), PLUGIN_VERSION );

	wp_enqueue_script( PLUGIN_HYPHEN . '-admin-standard', plugins_url( 'assets/js/admin-standard.js', PLUGIN_FILE ), array(), PLUGIN_VERSION, true );
}

/**
 * Enqueue the front-end stylesheet used by the widget and both shortcodes.
 */
function enqueue_frontend_assets() {
	wp_enqueue_style( PLUGIN_HYPHEN, plugins_url( 'assets/css/style.css', PLUGIN_FILE ), array(), PLUGIN_VERSION );
}
