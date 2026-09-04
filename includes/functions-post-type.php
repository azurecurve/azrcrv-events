<?php
/*
	custom post type and taxonomy functions
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
 * Create the event-categories taxonomy for the event post type.
 */
function create_taxonomy() {

	register_taxonomy(
		PLUGIN_TAXONOMY,
		PLUGIN_POST_TYPE,
		array(
			'label'        => esc_html__( 'Categories', 'azrcrv-e' ),
			'rewrite'      => array( 'slug' => PLUGIN_TAXONOMY ),
			'hierarchical' => true,
		)
	);
}

/**
 * Create the event custom post type.
 */
function create_post_type() {

	register_post_type(
		PLUGIN_POST_TYPE,
		array(
			'labels'             => array(
				'name'               => esc_html__( 'Events', 'azrcrv-e' ),
				'singular_name'      => esc_html__( 'Event', 'azrcrv-e' ),
				'add_new'            => esc_html__( 'Add New', 'azrcrv-e' ),
				'add_new_item'       => esc_html__( 'Add New Event', 'azrcrv-e' ),
				'edit'               => esc_html__( 'Edit', 'azrcrv-e' ),
				'edit_item'          => esc_html__( 'Edit Event', 'azrcrv-e' ),
				'new_item'           => esc_html__( 'New Event', 'azrcrv-e' ),
				'view'               => esc_html__( 'View', 'azrcrv-e' ),
				'view_item'          => esc_html__( 'View Event', 'azrcrv-e' ),
				'search_items'       => esc_html__( 'Search Event', 'azrcrv-e' ),
				'not_found'          => esc_html__( 'No Event found', 'azrcrv-e' ),
				'not_found_in_trash' => esc_html__( 'No Event found in Trash', 'azrcrv-e' ),
				'parent'             => esc_html__( 'Parent Event', 'azrcrv-e' ),
			),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'menu_position'       => 50,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'taxonomies'          => array( PLUGIN_TAXONOMY ),
			'menu_icon'           => 'dashicons-calendar-alt',
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
		)
	);
}

/**
 * Only filter admin gettext strings while viewing an 'event' screen, so
 * this plugin's excerpt-box relabelling doesn't leak onto other post types.
 */
function current_screen_callback( $screen ) {
	if ( is_object( $screen ) && PLUGIN_POST_TYPE === $screen->post_type ) {
		add_filter( 'gettext', __NAMESPACE__ . '\\admin_post_excerpt_change_labels', 99, 3 );
	}
}

/**
 * Relabel the Excerpt box as "Event Outline" on the event post type, and
 * hide its description text.
 */
function admin_post_excerpt_change_labels( $translation, $original ) {

	if ( 'Excerpt' === $original ) {
		return esc_html__( 'Event Outline', 'azrcrv-e' );
	}

	if ( false !== strpos( $original, 'Excerpts are optional hand-crafted summaries of your' ) ) {
		return '';
	}

	return $translation;
}
