<?php
/*
	event list table columns - adds Category, Start Date/Time, End
	Date/Time and Location columns to the Events admin list
	(edit.php?post_type=event), and makes Start Date sortable.
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
 * Add Category, Start Date/Time, End Date/Time and Location columns,
 * inserted after Title and before the built-in Date (published) column.
 */
function event_list_columns( $columns ) {

	$new_columns = array();

	foreach ( $columns as $key => $label ) {

		$new_columns[ $key ] = $label;

		if ( 'title' === $key ) {
			$new_columns['azrcrv-e-category']   = esc_html__( 'Category', 'azrcrv-e' );
			$new_columns['azrcrv-e-start-date'] = esc_html__( 'Start Date/Time', 'azrcrv-e' );
			$new_columns['azrcrv-e-end-date']   = esc_html__( 'End Date/Time', 'azrcrv-e' );
			$new_columns['azrcrv-e-location']   = esc_html__( 'Location', 'azrcrv-e' );
		}
	}

	return $new_columns;
}

/**
 * Render the custom column content.
 */
function render_event_list_column( $column, $post_id ) {

	if ( 'azrcrv-e-category' === $column ) {

		$terms = get_the_terms( $post_id, PLUGIN_TAXONOMY );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
		} else {
			echo '&#8212;';
		}

		return;
	}

	if ( 'azrcrv-e-start-date' === $column || 'azrcrv-e-end-date' === $column ) {

		$event_details = get_post_meta( $post_id, '_azrcrv_e_event_dates', true );

		if ( ! is_array( $event_details ) ) {
			echo '&#8212;';
			return;
		}

		if ( 'azrcrv-e-start-date' === $column ) {
			$date = $event_details['start-date'] ?? '';
			$time = $event_details['start-time'] ?? '';
		} else {
			$date = $event_details['end-date'] ?? '';
			$time = $event_details['end-time'] ?? '';
		}

		if ( '' === $date ) {
			echo '&#8212;';
			return;
		}

		echo esc_html( format_date_string( $date, 'd/m/Y' ) . ' ' . $time );

		return;
	}

	if ( 'azrcrv-e-location' === $column ) {

		$event_details = get_post_meta( $post_id, '_azrcrv_e_event_dates', true );

		$location = is_array( $event_details ) && ! empty( $event_details['location'] ) ? $event_details['location'] : '';

		if ( '' === $location ) {
			echo '&#8212;';
			return;
		}

		echo esc_html( $location );
	}
}

/**
 * Make the Start Date column sortable.
 */
function event_sortable_columns( $columns ) {
	$columns['azrcrv-e-start-date'] = 'azrcrv-e-start-date';
	return $columns;
}

/**
 * Sort the Events list by start date when requested, using the start-date
 * stored inside the '_azrcrv_e_event_dates' meta array. WordPress/
 * ClassicPress can't natively order by a key nested inside a serialized
 * meta array, so this sorts the fetched page of posts in PHP after the
 * query has run rather than at the database level - fine at the scale this
 * admin list is used at.
 */
function event_list_orderby( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( PLUGIN_POST_TYPE !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( 'azrcrv-e-start-date' !== $query->get( 'orderby' ) ) {
		return;
	}

	add_filter( 'the_posts', __NAMESPACE__ . '\\sort_events_by_start_date', 10, 2 );
}

/**
 * Sort a fetched set of event posts by their start date/time, ascending or
 * descending depending on the list table's current sort order.
 */
function sort_events_by_start_date( $posts, $query ) {

	remove_filter( 'the_posts', __NAMESPACE__ . '\\sort_events_by_start_date', 10 );

	if ( empty( $posts ) ) {
		return $posts;
	}

	$order = 'desc' === strtolower( (string) $query->get( 'order' ) ) ? 'desc' : 'asc';

	usort(
		$posts,
		function ( $a, $b ) {
			$a_details = get_post_meta( $a->ID, '_azrcrv_e_event_dates', true );
			$b_details = get_post_meta( $b->ID, '_azrcrv_e_event_dates', true );

			$a_key = ( is_array( $a_details ) ? ( $a_details['start-date'] ?? '' ) . ' ' . ( $a_details['start-time'] ?? '' ) : '' );
			$b_key = ( is_array( $b_details ) ? ( $b_details['start-date'] ?? '' ) . ' ' . ( $b_details['start-time'] ?? '' ) : '' );

			return strcmp( $a_key, $b_key );
		}
	);

	if ( 'desc' === $order ) {
		$posts = array_reverse( $posts );
	}

	return $posts;
}
