<?php
/*
	setup - registration of activation/deactivation hooks, actions and
	filters. The admin_post_azrcrv_e_save_settings handler is registered
	directly in functions-settings.php, alongside the functions it calls,
	and the CPT/taxonomy/metabox/widget/shortcode registrations are
	registered directly in their own functions-*.php files, alongside the
	functions they call.
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

// Activation / deactivation.
register_activation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\activate_plugin' );
register_deactivation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\deactivate_plugin' );

// Admin menu.
add_action( 'admin_menu', __NAMESPACE__ . '\\create_admin_menu' );
add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), __NAMESPACE__ . '\\add_plugin_action_link', 10, 2 );

// Update Manager: tell it where to find this plugin's own icon/banner
// images (assets/images) rather than falling back to a generic default, or
// - the pre-2.0.0 bug this fixes - to whichever other azurecurve plugin's
// filter callback happened to be registered last.
$plugin_slug_for_um = plugin_basename( trim( PLUGIN_FILE ) );
add_filter( 'codepotent_update_manager_' . $plugin_slug_for_um . '_image_path', __NAMESPACE__ . '\\custom_image_path' );
add_filter( 'codepotent_update_manager_' . $plugin_slug_for_um . '_image_url', __NAMESPACE__ . '\\custom_image_url' );

// Admin assets.
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' );

// Front-end assets.
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_assets' );

// Language.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_languages' );

// Custom post type and taxonomy.
add_action( 'init', __NAMESPACE__ . '\\create_taxonomy' );
add_action( 'init', __NAMESPACE__ . '\\create_post_type' );
add_action( 'current_screen', __NAMESPACE__ . '\\current_screen_callback' );

// Event Dates metabox.
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\add_event_dates_metabox' );
add_action( 'save_post', __NAMESPACE__ . '\\save_event_dates_metabox', 10, 1 );

// Events admin list table columns.
add_filter( 'manage_' . PLUGIN_POST_TYPE . '_posts_columns', __NAMESPACE__ . '\\event_list_columns' );
add_action( 'manage_' . PLUGIN_POST_TYPE . '_posts_custom_column', __NAMESPACE__ . '\\render_event_list_column', 10, 2 );
add_filter( 'manage_edit-' . PLUGIN_POST_TYPE . '_sortable_columns', __NAMESPACE__ . '\\event_sortable_columns' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\\event_list_orderby' );

// Widget.
add_action( 'widgets_init', __NAMESPACE__ . '\\register_events_widget' );
