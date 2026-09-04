<?php
/*
	datetime functions - a single, timezone-aware helper module used
	everywhere this plugin displays a date, compares a date against
	"today", or turns a date/time into a timestamp for wp_schedule_single_event().

	THE BUG THIS FIXES: the pre-2.0.0 plugin used PHP's bare date() and
	strtotime(), which both resolve in the *server's* default PHP timezone
	(often UTC on many hosts). wp_schedule_single_event() expects a UTC
	Unix timestamp computed from the *site's* configured timezone
	(Settings > General > Timezone). When those two timezones differ,
	scheduling a post/repost via strtotime( $date . ' ' . $time ) fires the
	cron job on the wrong day and/or at the wrong hour, and "is this event
	over yet" cut-offs compared against a bare date() can be a day off
	around midnight. Every call site that used to call date()/strtotime()
	for these purposes has been switched to use the functions below
	instead.
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
 * "Today", as a Y-m-d string, in the site's configured timezone rather than
 * the server's PHP default timezone. Use this (not date( 'Y-m-d' )) for any
 * "is this event/date over yet" comparison.
 */
function today() {
	return current_time( 'Y-m-d' );
}

/**
 * Build a site-timezone-aware Unix timestamp from a Y-m-d date string, an
 * optional H:i time string, and an optional "-N days"-style relative
 * modifier - replacing the pre-2.0.0 plugin's
 * strtotime( $date . ' ' . $time ) (server timezone) used to compute the
 * timestamp passed into wp_schedule_single_event() (which expects a UTC
 * timestamp derived from the site's timezone).
 *
 * @param string $date     A Y-m-d date string.
 * @param string $time     Optional H:i time string. Defaults to midnight.
 * @param string $modifier Optional relative date modifier applied before
 *                          the time is added, e.g. '-14 days'.
 *
 * @return int|false Unix timestamp, or false if $date could not be parsed.
 */
function site_timestamp( $date, $time = '00:00', $modifier = '' ) {

	if ( empty( $date ) ) {
		return false;
	}

	try {
		$datetime = new \DateTime( $date, wp_timezone() );
	} catch ( \Exception $e ) {
		return false;
	}

	if ( ! empty( $modifier ) ) {
		try {
			$datetime->modify( $modifier );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	if ( ! empty( $time ) && preg_match( '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time ) ) {
		list( $hours, $minutes ) = array_map( 'intval', explode( ':', $time ) );
		$datetime->setTime( $hours, $minutes, 0 );
	}

	return $datetime->getTimestamp();
}

/**
 * Format a Unix timestamp for display using the site's configured
 * timezone (via wp_date()), replacing the pre-2.0.0 plugin's bare
 * date( $format, $timestamp ), which rendered in the server's default PHP
 * timezone rather than the site's.
 *
 * @param int    $timestamp Unix timestamp.
 * @param string $format    PHP date format string.
 *
 * @return string
 */
function format_timestamp( $timestamp, $format ) {

	if ( empty( $timestamp ) ) {
		return '';
	}

	return wp_date( $format, (int) $timestamp );
}

/**
 * Format a Y-m-d (or similar parseable) date string for display using the
 * site's configured timezone, replacing the pre-2.0.0 plugin's
 * date_format( date_create( $date_string ), $format ) (server timezone via
 * DateTime's implicit default-timezone constructor).
 *
 * @param string $date_string A date string, e.g. from event postmeta.
 * @param string $format      PHP date format string.
 *
 * @return string Empty string if $date_string can't be parsed.
 */
function format_date_string( $date_string, $format ) {

	if ( empty( $date_string ) ) {
		return '';
	}

	try {
		$datetime = new \DateTime( $date_string, wp_timezone() );
	} catch ( \Exception $e ) {
		return '';
	}

	return wp_date( $format, $datetime->getTimestamp() );
}

/**
 * Format an event's start/end date+time for display, e.g.
 * "10/09/2026 10:00-16:00" for a one-day event, or
 * "10/09/2026 10:00 -13/09/2026 16:00" for a multi-day event.
 *
 * @param array  $event_details Event date meta - 'start-date', 'start-time',
 *                               'end-date', 'end-time'.
 * @param string $date_format   PHP date format string.
 *
 * @return string
 */
function format_event_date_range( $event_details, $date_format ) {

	$start_date = format_date_string( $event_details['start-date'] ?? '', $date_format );
	$start_time = $event_details['start-time'] ?? '';
	$end_time   = $event_details['end-time'] ?? '';

	if ( ( $event_details['start-date'] ?? '' ) === ( $event_details['end-date'] ?? '' ) ) {
		return "{$start_date} {$start_time}-{$end_time}";
	}

	$end_date = format_date_string( $event_details['end-date'] ?? '', $date_format );

	return "{$start_date} {$start_time} - {$end_date} {$end_time}";
}

/**
 * Whether a Y-m-d (or similar parseable) date string is today or in the
 * future, compared using the site's configured timezone. Used for "is this
 * event over yet" checks in the widget and both shortcodes - replacing
 * date_format( date_create( $date_string ), 'Y-m-d' ) >= date( 'Y-m-d' ).
 *
 * Also guards against a missing/unparseable date string (a defensive fix -
 * the pre-2.0.0 plugin would emit a PHP warning here for an event whose
 * postmeta was never saved, e.g. because it was drafted before the
 * metabox's required fields existed).
 *
 * @param string $date_string A date string, e.g. from event postmeta.
 *
 * @return bool
 */
function is_on_or_after_today( $date_string ) {

	if ( empty( $date_string ) ) {
		return false;
	}

	try {
		$datetime = new \DateTime( $date_string, wp_timezone() );
	} catch ( \Exception $e ) {
		return false;
	}

	return $datetime->format( 'Y-m-d' ) >= today();
}
