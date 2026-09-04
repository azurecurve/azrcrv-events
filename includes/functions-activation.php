<?php
/*
	activation / deactivation functions
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
 * Plugin activation.
 *
 * Registers the 'event' post type/taxonomy (also registered on 'init' -
 * see functions-post-type.php) so the rewrite flush below picks up its
 * rewrite rules immediately, rather than only after the next page load.
 * The pre-2.0.0 plugin never did this, meaning the /event-categories/...
 * archive URLs could 404 until something else happened to flush rewrite
 * rules.
 */
function activate_plugin() {
	create_taxonomy();
	create_post_type();
	flush_rewrite_rules();
}

/**
 * Plugin deactivation. Deliberately does NOT delete the settings option or
 * any event content - deactivating a plugin should not lose the admin's
 * configuration or data; that only happens on uninstall, and even then
 * event posts/postmeta are only removed if the admin has opted in via the
 * "Delete event data on uninstall" setting (see uninstall.php).
 */
function deactivate_plugin() {
	flush_rewrite_rules();
}
