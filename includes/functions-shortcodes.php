<?php
/*
	[events] and [event] shortcodes - share the query helpers defined in
	functions-widget.php (build_upcoming_events_sql() / build_upcoming_event_sql()),
	rather than each shortcode duplicating its own copy of the query as the
	pre-2.0.0 plugin did.
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

add_shortcode( 'events', __NAMESPACE__ . '\\shortcode_display_events' );
add_shortcode( 'event', __NAMESPACE__ . '\\shortcode_display_event' );

/**
 * Render the markup for a single event (shared by both shortcodes).
 */
function render_event_markup( $event, $width, $height, $date_format ) {

	$event_details = get_post_meta( $event->ID, '_azrcrv_e_event_dates', true );

	if ( ! is_array( $event_details ) || ! is_on_or_after_today( $event_details['end-date'] ?? '' ) ) {
		return '';
	}

	$output = '<div class="azrcrv-e-container">';

	if ( has_post_thumbnail( $event->ID ) ) {
		$image   = wp_get_attachment_image( get_post_thumbnail_id( $event->ID ), array( $width, $height ), '', array( 'class' => 'img-responsive alignleft', 'alt' => get_the_title( $event->ID ) ) );
		$output .= '<div class="azrcrv-e-image">' . $image . '</div>';
	}

	$output .= '<div class="azrcrv-e-details">';
	$output .= '<p><h3 class="azrcrv-e">' . esc_html( $event->post_title ) . '</h3></p>';

	$output .= '<p class="azrcrv-e-dates">' . esc_html( format_event_date_range( $event_details, $date_format ) ) . '</p>';

	if ( ! empty( $event_details['location'] ) ) {
		$output .= '<p class="azrcrv-e-location">' . esc_html( $event_details['location'] ) . '</p>';
	}

	if ( strlen( $event->post_excerpt ) > 0 ) {
		$output .= '<p class="azrcrv-e-excerpt">' . esc_html( $event->post_excerpt ) . '</p>';
	}

	if ( strlen( $event->post_content ) > 0 ) {
		$output .= wpautop( wp_kses_post( $event->post_content ) );
	}

	$output .= '</div>';
	$output .= '</div>';
	$output .= '<p class="azrcrv-e-clear"></p>';

	return $output;
}

/**
 * [events category="..." width="..." height="..." limit="..."]
 */
function shortcode_display_events( $atts, $content = null ) {

	global $wpdb;

	$settings = get_settings();

	$args = shortcode_atts(
		array(
			'category' => $settings['shortcode']['category'],
			'width'    => $settings['shortcode']['width'],
			'height'   => $settings['shortcode']['height'],
			'limit'    => $settings['shortcode']['limit'],
		),
		$atts
	);

	$date_format = $settings['shortcode']['date-format'];

	$events = $wpdb->get_results( build_upcoming_events_sql( $args['category'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- prepared in build_upcoming_events_sql().

	$output = '';
	$count  = 0;
	foreach ( $events as $event ) {

		$markup = render_event_markup( $event, $args['width'], $args['height'], $date_format );

		if ( '' === $markup ) {
			continue;
		}

		$output .= $markup;
		++$count;

		if ( $count === (int) $args['limit'] ) {
			break;
		}
	}

	if ( '' === $output ) {
		$output = sprintf(
			/* translators: %s: event category name. */
			esc_html__( 'No events found for category %s', 'azrcrv-e' ),
			'<em>' . esc_html( $args['category'] ) . '</em>'
		);
	}

	return $output;
}

/**
 * [event slug="..." width="..." height="..."]
 */
function shortcode_display_event( $atts, $content = null ) {

	global $wpdb;

	$settings = get_settings();

	$args = shortcode_atts(
		array(
			'slug'   => '',
			'width'  => $settings['shortcode']['width'],
			'height' => $settings['shortcode']['height'],
		),
		$atts
	);

	$date_format = $settings['shortcode']['date-format'];

	$event = $wpdb->get_row( build_upcoming_event_sql( $args['slug'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- prepared in build_upcoming_event_sql().

	if ( ! $event ) {
		return '<p>' . sprintf(
			/* translators: %s: event slug. */
			esc_html__( 'No %s events found.', 'azrcrv-e' ),
			'<em>' . esc_html( $args['slug'] ) . '</em>'
		) . '</p>';
	}

	$markup = render_event_markup( $event, $args['width'], $args['height'], $date_format );

	if ( '' === $markup ) {
		return '<p>' . sprintf(
			/* translators: %s: event slug. */
			esc_html__( 'No %s events found.', 'azrcrv-e' ),
			'<em>' . esc_html( $args['slug'] ) . '</em>'
		) . '</p>';
	}

	return $markup;
}
