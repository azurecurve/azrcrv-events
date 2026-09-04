<?php
/*
	shortcode settings tab
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
		<th><h3><?php esc_html_e( 'Shortcode', 'azrcrv-e' ); ?></h3></th>
	</tr>

	<tr>
		<th scope="row"><label for="shortcode-category"><?php esc_html_e( 'Default Category', 'azrcrv-e' ); ?></label></th>
		<td>
			<select name="shortcode-category" id="shortcode-category">
				<?php
				$taxonomies = get_categories(
					array(
						'orderby'    => 'name',
						'hide_empty' => false,
						'taxonomy'   => PLUGIN_TAXONOMY,
					)
				);
				foreach ( $taxonomies as $taxonomy ) {
					echo '<option value="' . esc_attr( $taxonomy->slug ) . '" ' . selected( $settings['shortcode']['category'], $taxonomy->slug, false ) . '>' . esc_html( $taxonomy->name ) . '</option>';
				}
				?>
			</select>
			<p class="description"><?php esc_html_e( 'Default category for shortcodes.', 'azrcrv-e' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="shortcode-width"><?php esc_html_e( 'Image Width', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="shortcode-width" type="number" min="1" id="shortcode-width" value="<?php echo esc_attr( $settings['shortcode']['width'] ); ?>" class="small-text" />
			<p class="description"><?php esc_html_e( 'Default image width in pixels.', 'azrcrv-e' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="shortcode-height"><?php esc_html_e( 'Image Height', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="shortcode-height" type="number" min="1" id="shortcode-height" value="<?php echo esc_attr( $settings['shortcode']['height'] ); ?>" class="small-text" />
			<p class="description"><?php esc_html_e( 'Default image height in pixels.', 'azrcrv-e' ); ?></p>
		</td>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="shortcode-limit"><?php esc_html_e( 'Limit', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="shortcode-limit" type="number" min="1" id="shortcode-limit" value="<?php echo esc_attr( $settings['shortcode']['limit'] ); ?>" class="small-text" />
			<p class="description"><?php esc_html_e( 'Default number of events to display.', 'azrcrv-e' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="shortcode-date-format"><?php esc_html_e( 'Date Format', 'azrcrv-e' ); ?></label></th>
		<td>
			<input name="shortcode-date-format" type="text" id="shortcode-date-format" value="<?php echo esc_attr( $settings['shortcode']['date-format'] ); ?>" class="short-text" />
			<p class="description"><?php esc_html_e( 'Default date format applied to new widgets.', 'azrcrv-e' ); ?></p>
		</td>
	</tr>

</table>
