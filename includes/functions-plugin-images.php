<?php
/*
	plugin images functions - filters are registered on the slug-specific
	hook name in setup.php (codepotent_update_manager_{slug}_image_path /
	_image_url), not the pre-2.0.0 plugin's generic, non-namespaced filter
	name, which fired for every azurecurve plugin using Update Manager on
	the same site and could leak icon/banner lookups between them.
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
 * Custom plugin image path.
 */
function custom_image_path( $path ) {
	if ( strpos( $path, PLUGIN_SLUG ) !== false ) {
		$path = plugin_dir_path( PLUGIN_FILE ) . 'assets/images';
	}
	return $path;
}

/**
 * Custom plugin image url.
 */
function custom_image_url( $url ) {
	if ( strpos( $url, PLUGIN_SLUG ) !== false ) {
		$url = plugin_dir_url( PLUGIN_FILE ) . 'assets/images';
	}
	return $url;
}
