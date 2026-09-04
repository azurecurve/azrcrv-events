<?php
/*
	settings functions - settings are stored as a single option (see
	SETTINGS_OPTION_NAME), shaped as:

	array(
		'widget'    => array( 'title', 'intro-text', 'category', 'width', 'height', 'limit', 'date-format', 'hide' ),
		'shortcode' => array( 'category', 'width', 'height', 'limit', 'date-format' ),
	)
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
 * Render the admin page (Widget/Shortcode/Instructions/Other Plugins, in
 * tabs).
 */
function display_admin_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-e' ) );
	}

	echo '<div class="wrap ' . esc_attr( PLUGIN_HYPHEN ) . '-wrap">';
	echo '<h1>';
		echo '<a href="' . esc_url( DEVELOPER_RAW_LINK ) . esc_attr( PLUGIN_SHORT_SLUG ) . '/"><img src="' . esc_url( plugins_url( '../assets/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="' . esc_attr( DEVELOPER_NAME ) . '" /></a>';
		echo esc_html( get_admin_page_title() );
	echo '</h1>';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flag, not a state-changing action.
	if ( isset( $_GET[ PLUGIN_HYPHEN . '-message' ] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$message_key = sanitize_key( wp_unslash( $_GET[ PLUGIN_HYPHEN . '-message' ] ) );
		render_admin_notice( $message_key );
	}

	require_once __DIR__ . '/tabs-output.php';

	echo '</div>';
}

/**
 * Show a dismissible admin notice for a given message key, set via a
 * redirect query arg after a save action.
 */
function render_admin_notice( $message_key ) {

	$messages = array(
		'settings-saved' => array( 'success', __( 'Settings saved.', 'azrcrv-e' ) ),
		'invalid-nonce'  => array( 'error', __( 'Security check failed - please try again.', 'azrcrv-e' ) ),
	);

	if ( ! isset( $messages[ $message_key ] ) ) {
		return;
	}

	list( $type, $text ) = $messages[ $message_key ];
	$css_class            = 'success' === $type ? 'notice-success' : 'notice-error';

	echo '<div class="notice ' . esc_attr( $css_class ) . ' is-dismissible"><p>' . esc_html( $text ) . '</p></div>';
}

/**
 * Build a redirect URL back to the admin page with a status message.
 */
function redirect_with_message( $message_key ) {
	$args = array(
		'page'                      => PLUGIN_HYPHEN,
		PLUGIN_HYPHEN . '-message' => $message_key,
	);
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
}

/**
 * The plugin's built-in default values.
 */
function get_builtin_settings() {
	return array(
		'general'   => array(
			// Off by default - uninstalling should not silently delete
			// content unless the admin has explicitly opted in.
			'delete-data-on-uninstall' => 0,
		),
		'widget'    => array(
			'title'       => esc_html__( 'Upcoming Events', 'azrcrv-e' ),
			'intro-text'  => '',
			'category'    => '',
			'width'       => 100,
			'height'      => 100,
			'limit'       => 10,
			'date-format' => 'd/m/Y',
			'hide'        => 0,
		),
		'shortcode' => array(
			'category'    => '',
			'width'       => 300,
			'height'      => 300,
			'limit'       => 10,
			'date-format' => 'd/m/Y',
		),
	);
}

/**
 * Recursively parse settings to merge with defaults, so every key is always
 * present even for a fresh install or a partially-saved option.
 */
function recursive_parse_args( $args, $defaults ) {

	$new_args = (array) $defaults;

	foreach ( (array) $args as $key => $value ) {
		if ( is_array( $value ) && isset( $new_args[ $key ] ) && is_array( $new_args[ $key ] ) ) {
			$new_args[ $key ] = recursive_parse_args( $value, $new_args[ $key ] );
		} else {
			$new_args[ $key ] = $value;
		}
	}

	return $new_args;
}

/**
 * Get the saved settings, merged over the built-in defaults.
 */
function get_settings() {

	$stored = get_option( SETTINGS_OPTION_NAME, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return recursive_parse_args( $stored, get_builtin_settings() );
}

/**
 * Persist the settings.
 */
function save_settings( $settings ) {
	update_option( SETTINGS_OPTION_NAME, $settings );
}

/**
 * Build a sanitized settings array from a submitted settings form ($_POST),
 * merged over the currently saved settings so fields on tabs not present in
 * the current submission are preserved rather than reset.
 */
function sanitize_settings_from_post( $post_data ) {

	$settings = get_settings();

	$settings['general']['delete-data-on-uninstall'] = isset( $post_data['delete-data-on-uninstall'] ) ? 1 : 0;

	if ( isset( $post_data['widget-title'] ) ) {
		$settings['widget']['title'] = sanitize_text_field( $post_data['widget-title'] );
	}
	if ( isset( $post_data['widget-intro-text'] ) ) {
		$settings['widget']['intro-text'] = sanitize_text_field( $post_data['widget-intro-text'] );
	}
	if ( isset( $post_data['widget-category'] ) ) {
		$settings['widget']['category'] = sanitize_text_field( $post_data['widget-category'] );
	}
	if ( isset( $post_data['widget-width'] ) ) {
		$settings['widget']['width'] = intval( $post_data['widget-width'] );
	}
	if ( isset( $post_data['widget-height'] ) ) {
		$settings['widget']['height'] = intval( $post_data['widget-height'] );
	}
	if ( isset( $post_data['widget-limit'] ) ) {
		$settings['widget']['limit'] = intval( $post_data['widget-limit'] );
	}
	if ( isset( $post_data['widget-date-format'] ) ) {
		$settings['widget']['date-format'] = sanitize_text_field( $post_data['widget-date-format'] );
	}
	$settings['widget']['hide'] = isset( $post_data['widget-hide'] ) ? 1 : 0;

	if ( isset( $post_data['shortcode-category'] ) ) {
		$settings['shortcode']['category'] = sanitize_text_field( $post_data['shortcode-category'] );
	}
	if ( isset( $post_data['shortcode-width'] ) ) {
		$settings['shortcode']['width'] = intval( $post_data['shortcode-width'] );
	}
	if ( isset( $post_data['shortcode-height'] ) ) {
		$settings['shortcode']['height'] = intval( $post_data['shortcode-height'] );
	}
	if ( isset( $post_data['shortcode-limit'] ) ) {
		$settings['shortcode']['limit'] = intval( $post_data['shortcode-limit'] );
	}
	if ( isset( $post_data['shortcode-date-format'] ) ) {
		$settings['shortcode']['date-format'] = sanitize_text_field( $post_data['shortcode-date-format'] );
	}

	return $settings;
}

/**
 * Handle the "Save Settings" form.
 */
function handle_save_settings() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action.', 'azrcrv-e' ) );
	}

	if ( ! isset( $_POST[ PLUGIN_HYPHEN . '-nonce' ] ) || ! check_admin_referer( PLUGIN_HYPHEN . '-save-settings', PLUGIN_HYPHEN . '-nonce' ) ) {
		redirect_with_message( 'invalid-nonce' );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above.
	$settings = sanitize_settings_from_post( wp_unslash( $_POST ) );

	save_settings( $settings );

	redirect_with_message( 'settings-saved' );
}
add_action( 'admin_post_' . PLUGIN_UNDERSCORE . '_save_settings', __NAMESPACE__ . '\\handle_save_settings' );
