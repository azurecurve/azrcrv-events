<?php
/*
	Event Details metabox (formerly "Event Dates") - lets an admin set the
	start/end date and time of an event, plus a free-text Location.
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

// Fixed nonce action, rather than the pre-2.0.0 plugin's basename( __FILE__ )
// (which pointed at the top-level plugin file, and would silently change -
// breaking the nonce check - once this code moved into its own includes
// file during the 2.0.0 restructure).
const EVENT_DATES_NONCE_ACTION = PLUGIN_UNDERSCORE . '_event_dates';

/**
 * Add the Event Details metabox to the sidebar.
 *
 * Note: the metabox id, function names and file name keep their original
 * "event-dates"/"event_dates" naming (only the on-screen label changed to
 * "Event Details") to avoid unnecessarily widening this change - a nonce
 * action tied to the old name still needs to keep matching between
 * render_event_dates_metabox() and save_event_dates_metabox().
 */
function add_event_dates_metabox() {
	add_meta_box( PLUGIN_HYPHEN . '-event-dates-box', esc_html__( 'Event Details', 'azrcrv-e' ), __NAMESPACE__ . '\\render_event_dates_metabox', array( PLUGIN_POST_TYPE ), 'side', 'default' );
}

/**
 * Render the Event Details metabox markup.
 */
function render_event_dates_metabox() {

	global $post;

	wp_nonce_field( EVENT_DATES_NONCE_ACTION, 'azrcrv-e-event-dates-nonce' );

	$event_dates = get_post_meta( $post->ID, '_azrcrv_e_event_dates', true );

	$start_date = isset( $event_dates['start-date'] ) ? $event_dates['start-date'] : '';
	$start_time = isset( $event_dates['start-time'] ) ? $event_dates['start-time'] : '';
	$end_time   = isset( $event_dates['end-time'] ) ? $event_dates['end-time'] : '';
	$end_date   = isset( $event_dates['end-date'] ) ? $event_dates['end-date'] : '';
	$location   = isset( $event_dates['location'] ) ? $event_dates['location'] : '';
	?>

	<fieldset>
		<table>
			<tr>
				<td><?php esc_html_e( 'Start Date: ', 'azrcrv-e' ); ?></td>
				<td>
					<input type="date" id="start-date" name="start-date" value="<?php echo esc_attr( $start_date ); ?>" required />
				</td>
			</tr>

			<tr>
				<td><?php esc_html_e( 'Start Time: ', 'azrcrv-e' ); ?></td>
				<td>
					<input type="time" id="start-time" name="start-time" value="<?php echo esc_attr( $start_time ); ?>" required />
				</td>
			</tr>

			<tr>
				<td><?php esc_html_e( 'End Time:', 'azrcrv-e' ); ?></td>
				<td>
					<input type="time" id="end-time" name="end-time" value="<?php echo esc_attr( $end_time ); ?>" required />
				</td>
			</tr>

			<tr>
				<td><?php esc_html_e( 'End Date:', 'azrcrv-e' ); ?></td>
				<td>
					<input type="date" id="end-date" name="end-date" value="<?php echo esc_attr( $end_date ); ?>" />
				</td>
			</tr>

			<tr>
				<td><?php esc_html_e( 'Location:', 'azrcrv-e' ); ?></td>
				<td>
					<input type="text" id="location" name="location" value="<?php echo esc_attr( $location ); ?>" class="widefat" maxlength="255" />
				</td>
			</tr>
		</table>
		<p>
			<em><?php esc_html_e( 'For a one day event, do not set an end date.', 'azrcrv-e' ); ?></em>
		</p>
	</fieldset>

	<?php
}

/**
 * Save the Event Dates metabox.
 */
function save_event_dates_metabox( $post_id ) {

	if ( ! isset( $_POST['azrcrv-e-event-dates-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['azrcrv-e-event-dates-nonce'] ) ), EVENT_DATES_NONCE_ACTION ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( PLUGIN_POST_TYPE !== get_post_type( $post_id ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above.
	$posted = wp_unslash( $_POST );

	$start_date = isset( $posted['start-date'] ) ? preg_replace( '([^0-9-])', '', $posted['start-date'] ) : '';
	$end_date   = isset( $posted['end-date'] ) ? preg_replace( '([^0-9-])', '', $posted['end-date'] ) : '';

	// The End Date field is optional (see the help text below it in the
	// metabox); a blank End Date means a one-day event, so default it to
	// the Start Date rather than leaving it blank - the rest of the plugin
	// (widget, shortcodes, "is this event over" checks) relies on End Date
	// always being set to a valid date.
	if ( '' === $end_date ) {
		$end_date = $start_date;
	}

	$location = isset( $posted['location'] ) ? sanitize_text_field( $posted['location'] ) : '';

	update_post_meta(
		$post_id,
		'_azrcrv_e_event_dates',
		array(
			'start-date' => $start_date,
			'start-time' => isset( $posted['start-time'] ) ? preg_replace( '([^0-9:])', '', $posted['start-time'] ) : '',
			'end-time'   => isset( $posted['end-time'] ) ? preg_replace( '([^0-9:])', '', $posted['end-time'] ) : '',
			'end-date'   => $end_date,
			'location'   => $location,
		)
	);

	// The pre-2.0.0 plugin returned esc_attr( $_POST['autopost'] ) here -
	// 'autopost' is never a field posted by this form (leftover/copy-paste
	// from elsewhere), and WordPress/ClassicPress ignores the return value
	// of a 'save_post' callback in any case, so this function simply
	// returns nothing.
}
