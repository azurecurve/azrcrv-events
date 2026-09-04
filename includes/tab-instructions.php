<?php
/*
	instructions tab
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
?>

<h2><?php esc_html_e( 'Instructions', 'azrcrv-e' ); ?></h2>

<p><?php esc_html_e( 'This plugin lets you create events - holidays, webinars, achievements, notable historical figures, or anything else with a start (and optional end) date - and display upcoming ones in a widget or via a shortcode.', 'azrcrv-e' ); ?></p>

<ol>
	<li><?php esc_html_e( 'Add a new Event from the Events menu, and give it a title, an outline (shown as the excerpt), and optionally a body and featured image.', 'azrcrv-e' ); ?></li>
	<li><?php esc_html_e( 'Fill in its Event Details in the sidebar metabox - a start date and time are required; only set an end date if the event runs across more than one day. Location is a free-text field and is optional.', 'azrcrv-e' ); ?></li>
	<li><?php esc_html_e( 'Optionally assign one or more Categories to the event, so it can be filtered in the widget or shortcode.', 'azrcrv-e' ); ?></li>
	<li><?php esc_html_e( 'Publish the event. It will stop appearing in the widget/shortcode output once its end date (or start date, for a one-day event) has passed.', 'azrcrv-e' ); ?></li>
</ol>

<h3><?php esc_html_e( 'Widget', 'azrcrv-e' ); ?></h3>
<p><?php esc_html_e( 'Add the Events widget to a sidebar from Appearance > Widgets. Its default title, category, image size, and date format can be set on the Widget tab, and overridden per-widget-instance.', 'azrcrv-e' ); ?></p>

<h3><?php esc_html_e( 'Shortcodes', 'azrcrv-e' ); ?></h3>
<p>
	<?php printf( /* translators: %s: example [event] shortcode. */ esc_html__( 'Display a single event by its slug: %s', 'azrcrv-e' ), '<code>[event slug="december-2021" width=100 height=100]</code>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
</p>
<p>
	<?php printf( /* translators: %s: example [events] shortcode. */ esc_html__( 'Display multiple upcoming events, optionally filtered by category: %s', 'azrcrv-e' ), '<code>[events category="webinars" width=150 height=150 limit=3]</code>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
</p>

<h3><?php esc_html_e( 'Timezones', 'azrcrv-e' ); ?></h3>
<p><?php esc_html_e( 'All "is this event over yet" checks use your site\'s configured timezone (Settings > General > Timezone), not the timezone of the server itself.', 'azrcrv-e' ); ?></p>
