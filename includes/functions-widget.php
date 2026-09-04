<?php
/*
	Events widget - lists upcoming events, optionally filtered by category.
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
 * Register the widget.
 */
function register_events_widget() {
	register_widget( __NAMESPACE__ . '\\Events_Widget' );
}

/**
 * Build the SQL to select published events, optionally filtered by
 * category, ordered by post date.
 */
function build_upcoming_events_sql( $category ) {

	global $wpdb;

	$sql = "SELECT p.ID, p.post_title, p.post_content, p.post_excerpt FROM
				{$wpdb->posts} AS p
			INNER JOIN {$wpdb->term_relationships} AS tr ON tr.object_id = p.ID
			INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->terms} AS t ON t.term_id = tt.term_id
			WHERE t.slug = %s
			AND p.post_status = 'publish'
			AND p.post_type = %s
			ORDER BY p.post_date ASC";

	return $wpdb->prepare( $sql, $category, PLUGIN_POST_TYPE ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are not user input.
}

/**
 * Build the SQL to select a single published event by its slug.
 */
function build_upcoming_event_sql( $slug ) {

	global $wpdb;

	$sql = "SELECT p.ID, p.post_title, p.post_content, p.post_excerpt FROM
				{$wpdb->posts} AS p
			WHERE p.post_name = %s
			AND p.post_status = 'publish'
			AND p.post_type = %s";

	return $wpdb->prepare( $sql, $slug, PLUGIN_POST_TYPE ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are not user input.
}

/**
 * Events widget class.
 */
class Events_Widget extends \WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		parent::__construct(
			PLUGIN_HYPHEN,
			esc_html__( 'Events by azurecurve', 'azrcrv-e' ),
			array( 'description' => esc_html__( 'Events in a widget', 'azrcrv-e' ) )
		);
	}

	/**
	 * Enqueue front-end styles for this widget. Kept for backwards
	 * compatibility with any theme relying on this hook running - the
	 * plugin-wide stylesheet is otherwise already enqueued via
	 * functions-scripts.php's enqueue_frontend_assets().
	 */
	public function enqueue() {
		wp_enqueue_style( PLUGIN_HYPHEN, plugins_url( 'assets/css/style.css', PLUGIN_FILE ), array(), '2.0.0' );
	}

	/**
	 * Display the widget form in wp-admin.
	 */
	public function form( $instance ) {

		$settings = get_settings();

		$title       = ! empty( $instance['title'] ) ? $instance['title'] : $settings['widget']['title'];
		$intro_text  = ! empty( $instance['intro-text'] ) ? $instance['intro-text'] : $settings['widget']['intro-text'];
		$category    = ! empty( $instance['category'] ) ? $instance['category'] : $settings['widget']['category'];
		$width       = ! empty( $instance['width'] ) ? $instance['width'] : $settings['widget']['width'];
		$height      = ! empty( $instance['height'] ) ? $instance['height'] : $settings['widget']['height'];
		$limit       = ! empty( $instance['limit'] ) ? $instance['limit'] : $settings['widget']['limit'];
		$date_format = ! empty( $instance['date-format'] ) ? $instance['date-format'] : $settings['widget']['date-format'];
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'azrcrv-e' ); ?>&nbsp;
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'intro-text' ) ); ?>">
				<?php esc_html_e( 'Intro Text:', 'azrcrv-e' ); ?>&nbsp;
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'intro-text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'intro-text' ) ); ?>" value="<?php echo esc_attr( $intro_text ); ?>" class="widefat" />
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
				<?php esc_html_e( 'Category:', 'azrcrv-e' ); ?>&nbsp;
				<select id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
					<?php
					$taxonomies = get_categories(
						array(
							'orderby'    => 'name',
							'hide_empty' => false,
							'taxonomy'   => PLUGIN_TAXONOMY,
						)
					);
					foreach ( $taxonomies as $taxonomy ) {
						echo '<option value="' . esc_attr( $taxonomy->slug ) . '" ' . selected( $category, $taxonomy->slug, false ) . '>' . esc_html( $taxonomy->name ) . '</option>';
					}
					?>
				</select>
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>">
				<?php esc_html_e( 'Width:', 'azrcrv-e' ); ?>&nbsp;
				<input type="number" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" class="small-text" value="<?php echo esc_attr( $width ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>">
				<?php esc_html_e( 'Height:', 'azrcrv-e' ); ?>&nbsp;
				<input type="number" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" class="small-text" value="<?php echo esc_attr( $height ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_html_e( 'Limit:', 'azrcrv-e' ); ?>&nbsp;
				<input type="number" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" class="small-text" value="<?php echo esc_attr( $limit ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date-format' ) ); ?>">
				<?php esc_html_e( 'Date Format:', 'azrcrv-e' ); ?>&nbsp;
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'date-format' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'date-format' ) ); ?>" value="<?php echo esc_attr( $date_format ); ?>" />
			</label>
		</p>

		<?php
	}

	/**
	 * Sanitize widget settings on save.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']       = sanitize_text_field( $new_instance['title'] );
		$instance['intro-text']  = wp_kses( $new_instance['intro-text'], wp_kses_allowed_html( 'post' ) );
		$instance['category']    = sanitize_text_field( $new_instance['category'] );
		$instance['width']       = intval( $new_instance['width'] );
		$instance['height']      = intval( $new_instance['height'] );
		$instance['limit']       = intval( $new_instance['limit'] );
		$instance['date-format'] = sanitize_text_field( $new_instance['date-format'] );

		return $instance;
	}

	/**
	 * Display the widget on the front end.
	 */
	public function widget( $args, $instance ) {

		global $wpdb;

		$settings = get_settings();

		// Explicit $args[...] references, rather than the pre-2.0.0
		// plugin's extract( $args ), which pollutes the local scope with
		// variable-variables and makes the code harder to audit.
		$before_widget = $args['before_widget'];
		$after_widget  = $args['after_widget'];
		$before_title  = $args['before_title'];
		$after_title   = $args['after_title'];

		$output = $before_widget;
		$output .= $before_title;
		$title   = ! empty( $instance['title'] ) ? $instance['title'] : $settings['widget']['title'];
		$output .= apply_filters( 'widget_title', $title );
		$output .= $after_title;

		$intro_text  = ! empty( $instance['intro-text'] ) ? $instance['intro-text'] : '';
		$date_format = ! empty( $instance['date-format'] ) ? $instance['date-format'] : $settings['widget']['date-format'];
		if ( strlen( $intro_text ) > 0 ) {
			$output .= '<p>' . wp_kses( $intro_text, wp_kses_allowed_html( 'post' ) ) . '</p>';
		}

		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		$events   = $wpdb->get_results( build_upcoming_events_sql( $category ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- prepared in build_upcoming_events_sql().

		$count = 0;
		foreach ( $events as $event ) {

			$event_details = get_post_meta( $event->ID, '_azrcrv_e_event_dates', true );

			if ( ! is_array( $event_details ) || ! is_on_or_after_today( $event_details['end-date'] ?? '' ) ) {
				continue;
			}

			++$count;

			$output .= '<div class="azrcrv-e-container-widget">';
			$title   = $event->post_title;

			if ( has_post_thumbnail( $event->ID ) ) {
				$image   = wp_get_attachment_image( get_post_thumbnail_id( $event->ID ), array( $instance['width'], $instance['height'] ), '', array( 'class' => 'img-responsive alignleft', 'alt' => get_the_title( $event->ID ) ) );
				$output .= '<div class="azrcrv-e-widget-image">' . $image . '</div>';
			}

			$output .= '<div class="azrcrv-e-widget-details">';
			$output .= '<p><h3 class="azrcrv-e">' . esc_html( $title ) . '</h3></p>';

			$output .= '<p class="azrcrv-e-widget-dates">' . esc_html( format_event_date_range( $event_details, $date_format ) ) . '</p>';

			if ( ! empty( $event_details['location'] ) ) {
				$output .= '<p class="azrcrv-e-widget-location">' . esc_html( $event_details['location'] ) . '</p>';
			}

			$output .= '<p class="azrcrv-e-widget-excerpt">' . esc_html( $event->post_excerpt ) . '</p>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<p class="azrcrv-e-clear"></p>';

			if ( $count === (int) $instance['limit'] ) {
				break;
			}
		}

		if ( 0 === $count ) {
			$output .= '<p>' . sprintf(
				/* translators: %s: event category name. */
				esc_html__( 'No %s events found.', 'azrcrv-e' ),
				'<em>' . esc_html( $category ) . '</em>'
			) . '</p>';
		}

		$output .= $after_widget;

		if ( $count >= 1 || ( 1 !== (int) $settings['widget']['hide'] && 0 === $count ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $output is assembled from already-escaped fragments and trusted static markup above.
			echo $output;
		}
	}
}
