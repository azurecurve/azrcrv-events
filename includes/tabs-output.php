<?php
/*
	tab output on the admin page - markup/classes match the shared
	azrcrv-ui-tabs component used across azurecurve's plugins (see
	assets/css/admin-standard.css and assets/js/admin-standard.js), rather
	than ClassicPress's native nav-tab-wrapper used by the pre-2.0.0
	plugin.

	Unlike Shortcodes in Comments (which has a single settings tab), this
	plugin has two settings tabs (Widget/Shortcode) that both need to submit
	together as one form, plus Instructions and Other Plugins tabs - so,
	unlike SIC's tabs-output.php, the <form> here wraps the whole tab set
	rather than living inside a single tab.
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

$tab_widget_label       = esc_html__( 'Widget', 'azrcrv-e' );
$tab_shortcode_label    = esc_html__( 'Shortcode', 'azrcrv-e' );
$tab_instructions_label = esc_html__( 'Instructions', 'azrcrv-e' );

ob_start();
require_once __DIR__ . '/tab-widget.php';
$tab_widget = ob_get_clean();

ob_start();
require_once __DIR__ . '/tab-shortcode.php';
$tab_shortcode = ob_get_clean();

ob_start();
require_once __DIR__ . '/tab-instructions.php';
$tab_instructions = ob_get_clean();

// tab-other-plugins.php sets $tab_plugins_label and $tab_plugins directly
// (matching the shared pattern used across azurecurve's other plugins)
// rather than being captured via ob_start(), since it builds its output as
// a string rather than echoing it.
require_once __DIR__ . '/tab-other-plugins.php';
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<input type="hidden" name="action" value="<?php echo esc_attr( PLUGIN_UNDERSCORE ); ?>_save_settings" />
	<?php wp_nonce_field( PLUGIN_HYPHEN . '-save-settings', PLUGIN_HYPHEN . '-nonce' ); ?>

	<p>
		<?php esc_html_e( 'Events allows events to be created and displayed in a widget or using a shortcode.', 'azrcrv-e' ); ?>
	</p>
	<p>
		<?php printf( /* translators: %s: example [event] shortcode. */ esc_html__( 'The shortcode for displaying a single event is %s', 'azrcrv-e' ), '<strong>[event slug="december-' . date("Y") . '" width=100 height=100]</strong>' ); ?>
	</p>
	<p>
		<?php printf( /* translators: %s: example [events] shortcode. */ esc_html__( 'The shortcode for displaying multiple events is %s', 'azrcrv-e' ), '<strong>[events category="webinars" width=150 height=150 limit=3]</strong>' ); ?>
	</p>

	<div id="tabs" class="azrcrv-ui-tabs">
		<ul class="azrcrv-ui-tabs-nav azrcrv-ui-widget-header" role="tablist">
			<li class="azrcrv-ui-state-default azrcrv-ui-state-active" aria-controls="tab-panel-widget" aria-labelledby="tab-widget" aria-selected="true" aria-expanded="true" role="tab">
				<a id="tab-widget" class="azrcrv-ui-tabs-anchor" href="#tab-panel-widget"><?php echo $tab_widget_label; // phpcs:ignore. ?></a>
			</li>
			<li class="azrcrv-ui-state-default" aria-controls="tab-panel-shortcode" aria-labelledby="tab-shortcode" aria-selected="false" aria-expanded="false" role="tab">
				<a id="tab-shortcode" class="azrcrv-ui-tabs-anchor" href="#tab-panel-shortcode"><?php echo $tab_shortcode_label; // phpcs:ignore. ?></a>
			</li>
			<li class="azrcrv-ui-state-default" aria-controls="tab-panel-instructions" aria-labelledby="tab-instructions" aria-selected="false" aria-expanded="false" role="tab">
				<a id="tab-instructions" class="azrcrv-ui-tabs-anchor" href="#tab-panel-instructions"><?php echo $tab_instructions_label; // phpcs:ignore. ?></a>
			</li>
			<li class="azrcrv-ui-state-default" aria-controls="tab-panel-plugins" aria-labelledby="tab-plugins" aria-selected="false" aria-expanded="false" role="tab">
				<a id="tab-plugins" class="azrcrv-ui-tabs-anchor" href="#tab-panel-plugins"><?php echo $tab_plugins_label; // phpcs:ignore. ?></a>
			</li>
		</ul>
		<div id="tab-panel-widget" class="azrcrv-ui-tabs-scroll" role="tabpanel" aria-hidden="false">
			<fieldset>
				<legend class="screen-reader-text"><?php echo $tab_widget_label; // phpcs:ignore. ?></legend>
				<?php echo $tab_widget; // phpcs:ignore. ?>
			</fieldset>
		</div>
		<div id="tab-panel-shortcode" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
			<fieldset>
				<legend class="screen-reader-text"><?php echo $tab_shortcode_label; // phpcs:ignore. ?></legend>
				<?php echo $tab_shortcode; // phpcs:ignore. ?>
			</fieldset>
		</div>
		<div id="tab-panel-instructions" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
			<fieldset>
				<legend class="screen-reader-text"><?php echo $tab_instructions_label; // phpcs:ignore. ?></legend>
				<?php echo $tab_instructions; // phpcs:ignore. ?>
			</fieldset>
		</div>
		<div id="tab-panel-plugins" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
			<fieldset>
				<legend class="screen-reader-text"><?php echo $tab_plugins_label; // phpcs:ignore. ?></legend>
				<?php echo $tab_plugins; // phpcs:ignore. ?>
			</fieldset>
		</div>
	</div>

	<?php submit_button( __( 'Save Changes', 'azrcrv-e' ) ); ?>
</form>

<?php
/*
	donate button
*/
?>
<div class="azrcrv-donate">
	<?php esc_html_e( 'Support', 'azrcrv-e' ); ?>
	azurecurve | Development
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="MCJQN9SJZYLWJ">
		<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
	</form>
	<span>
		<?php esc_html_e( 'You can help support the development of our free plugins by donating a small amount of money.', 'azrcrv-e' ); ?>
	</span>
</div>
