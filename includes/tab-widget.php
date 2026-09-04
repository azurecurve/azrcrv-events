<?php
/*
	widget settings tab
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

$settings = get_settings();
?>

<table class="form-table">

	<tr>
		<th><h3><?php esc_html_e( 'Widget', 'azrcrv-e' ); ?></h3></th>
	</tr>

	<tr>
		<th scope="row"><label for="widget-title"><?php esc_html_e( 'Title', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="widget-title" type="text" id="widget-title" value="<?php echo esc_attr( $settings['widget']['title'] ); ?>" class="regular-text" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-intro-text"><?php esc_html_e( 'Intro Text', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="widget-intro-text" type="text" id="widget-intro-text" value="<?php echo esc_attr( $settings['widget']['intro-text'] ); ?>" class="large-text" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-category"><?php esc_html_e( 'Default Category', 'azrcrv-e' ); ?></label></th>
		<td>
			<select name="widget-category" id="widget-category">
				<?php
				$taxonomies = get_categories(
					array(
						'orderby'    => 'name',
						'hide_empty' => false,
						'taxonomy'   => PLUGIN_TAXONOMY,
					)
				);
				foreach ( $taxonomies as $taxonomy ) {
					echo '<option value="' . esc_attr( $taxonomy->slug ) . '" ' . selected( $settings['widget']['category'], $taxonomy->slug, false ) . '>' . esc_html( $taxonomy->name ) . '</option>';
				}
				?>
			</select>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-width"><?php esc_html_e( 'Width', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="widget-width" type="number" min="1" id="widget-width" value="<?php echo esc_attr( $settings['widget']['width'] ); ?>" class="small-text" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-height"><?php esc_html_e( 'Height', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="widget-height" type="number" min="1" id="widget-height" value="<?php echo esc_attr( $settings['widget']['height'] ); ?>" class="small-text" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-limit"><?php esc_html_e( 'Limit', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="widget-limit" type="number" min="1" id="widget-limit" value="<?php echo esc_attr( $settings['widget']['limit'] ); ?>" class="small-text" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-date-format"><?php esc_html_e( 'Date Format', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="widget-date-format" type="text" id="widget-date-format" value="<?php echo esc_attr( $settings['widget']['date-format'] ); ?>" class="short-text" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="widget-hide"><?php esc_html_e( 'Hide widget?', 'azrcrv-e' ); ?></label></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><span><?php esc_html_e( 'Hide widget when no events found?', 'azrcrv-e' ); ?></span></legend>
				<label for="widget-hide">
					<input name="widget-hide" type="checkbox" id="widget-hide" value="1" <?php checked( 1, (int) $settings['widget']['hide'] ); ?> />
					<?php esc_html_e( 'Hide widget when no events found.', 'azrcrv-e' ); ?>
				</label>
			</fieldset>
		</td>
	</tr>

	<tr>
		<th><h3><?php esc_html_e( 'General', 'azrcrv-e' ); ?></h3></th>
	</tr>

	<tr>
		<th scope="row"><label for="delete-data-on-uninstall"><?php esc_html_e( 'Delete event data on uninstall?', 'azrcrv-e' ); ?></label></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><span><?php esc_html_e( 'Delete all event posts and their metadata when this plugin is uninstalled?', 'azrcrv-e' ); ?></span></legend>
				<label for="delete-data-on-uninstall">
					<input name="delete-data-on-uninstall" type="checkbox" id="delete-data-on-uninstall" value="1" <?php checked( 1, (int) $settings['general']['delete-data-on-uninstall'] ); ?> />
					<?php esc_html_e( 'Delete all event posts and their metadata when this plugin is uninstalled. Leave unchecked to keep your events if you uninstall the plugin.', 'azrcrv-e' ); ?>
				</label>
			</fieldset>
		</td>
	</tr>

</table>
