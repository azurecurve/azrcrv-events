<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Events
 * Description: Announce holidays, events, achievements and notable historical figures in a widget.
 * Version: 1.0.0
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/azrcrv-events/
 * Text Domain: events
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname( __FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_e');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */
// add actions
add_action('admin_menu', 'azrcrv_e_create_admin_menu');
add_action('init', 'azrcrv_e_create_cust_taxonomy_for_custom_post');
add_action('init', 'azrcrv_e_create_custom_post_type');
add_action('current_screen', 'azrcrv_e_current_screen_callback');
add_action('admin_menu', 'azrcrv_e_add_event_dates_metabox');
add_action('save_post', 'azrcrv_e_save_event_dates_metabox', 10, 1);
add_action('admin_post_azrcrv_e_save_options', 'azrcrv_e_save_options');
add_action('plugins_loaded', 'azrcrv_e_load_languages');
add_action('wp_enqueue_scripts', 'azrcrv_e_load_css');
add_action('widgets_init', 'azrcrv_e_create_widget');

// add filters
add_filter('plugin_action_links', 'azrcrv_e_add_plugin_action_link', 10, 2);
add_filter('codepotent_update_manager_image_path', 'azrcrv_e_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_e_custom_image_url');

// add shortcodes
add_shortcode('events', 'azrcrv_n_display_events');
add_shortcode('event', 'azrcrv_n_display_event');

/**
 * Custom plugin image path.
 *
 * @since 1.12.0
 *
 */
function azrcrv_e_custom_image_path($path){
    if (strpos($path, 'azrcrv-events') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.12.0
 *
 */
function azrcrv_e_custom_image_url($url){
    if (strpos($url, 'azrcrv-events') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('events', false, $plugin_rel_path);
}

/**
 * Load plugin css.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_load_css(){
	wp_enqueue_style('azrcrv-e', plugins_url('assets/css/style.css', __FILE__));
}

/**
 * Get options including defaults.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_get_option($option_name){
 
	$defaults = array(
						'widget' => array(
											'title' => 'Upcoming Events',
											'intro-text' => '',
											'category' => '',
											'width' => 100,
											'height' => 100,
											'limit' => 10,
											'date-format' => 'm/d/Y',
										),
						'shortcode' => array(
												'category' => '',
												'width' => 300,
												'height' => 300,
												'limit' => 10,
												'date-format' => 'm/d/Y',
											),
					);

	$options = get_option($option_name, $defaults);

	$options = wp_parse_args($options, $defaults);

	return $options;

}

/**
 * Create custom events post type.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_create_cust_taxonomy_for_custom_post() {

	register_taxonomy(
						'event-categories',
						'event',
						array(
						'label' => __( 'Categories' ),
						'rewrite' => array( 'slug' => 'event-categories' ),
						'hierarchical' => true,
					)
	);

}

/**
 * Create custom event post type.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_create_custom_post_type(){
	register_post_type('event',
		array(
				'labels' => array(
									'name' => esc_html__('Events', 'events'),
									'singular_name' => esc_html__('Event', 'events'),
									'add_new' => esc_html__('Add New', 'events'),
									'add_new_item' => esc_html__('Add New Event', 'events'),
									'edit' => esc_html__('Edit', 'events'),
									'edit_item' => esc_html__('Edit Event', 'events'),
									'new_item' => esc_html__('New Event', 'events'),
									'view' => esc_html__('View', 'events'),
									'view_item' => esc_html__('View Event', 'events'),
									'search_items' => esc_html__('Search Event', 'events'),
									'not_found' => esc_html__('No Event found', 'events'),
									'not_found_in_trash' => esc_html__('No Event found in Trash', 'events'),
									'parent' => esc_html__('Parent Event', 'events')
								),
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'menu_position' => 50,
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
			'taxonomies' => array('event-categories'),
			'menu_icon' => 'dashicons-calendar-alt',
			'has_archive' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => false,
			'show_in_rest' => false,
		)
	);
}

/**
 * Make sure labels only changes for this post type
 *
 * @since 1.0.1
 *
 */
function azrcrv_e_current_screen_callback($screen) {
    if( is_object($screen) && $screen->post_type == 'event' ) {
        add_filter( 'gettext', 'azrcrv_e_admin_post_excerpt_change_labels', 99, 3 );
    }
}

/**
 * Change labels in the excerpt box
 *
 * @since 1.0.0
 *
 */ 
function azrcrv_e_admin_post_excerpt_change_labels($translation, $original){
	if ('Excerpt' == $original){
		return __('Event Outline', 'events');
	}else{
		$pos = strpos($original, 'Excerpts are optional hand-crafted summaries of your');

		if ($pos !== false){
			return  '';
		}
	}
	
	return $translation;
}

/**
 * Add post metabox to sidebar.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_add_event_dates_metabox(){
	add_meta_box('azrcrv-e-box', esc_html__('Event Dates', 'events'), 'azrcrv_e_generate_event_dates_metabox', array('event'), 'side', 'default');	
}

/**
 * Generate post sidebar metabox.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_generate_event_dates_metabox(){
	
	global $post;
	
	wp_nonce_field(basename(__FILE__), 'azrcrv-e-event-dates-nonce');
	
	$event_dates = get_post_meta($post->ID, '_azrcrv_e_event_dates', true);
		
	?>
	
	<fieldset>
		<table>
			<tr>
				<td>
					<?php _e('Start Date: ', 'events'); ?>
				</td>
				<td>
					<input type="date" id="start-date" name="start-date" value="<?php echo $event_dates['start-date']; ?>" required />
				</td>
			</tr>
			
			<tr>
				<td>
					<?php _e('Start Time: ', 'events'); ?>
				</td>
				<td>
					<input type="time" id="start-time" name="start-time" value="<?php echo $event_dates['start-time']; ?>" required />
				</td>
			</tr>
		
			<tr>
				<td>
					<?php _e('End Time:', 'events'); ?>
				</td>
				<td>
					<input type="time" id="end-time" name="end-time" value="<?php echo $event_dates['end-time']; ?>" required />
				</td>
			</tr>
		
			<tr>
				<td>
					<?php _e('End Date:', 'events'); ?>
				</td>
				<td>
					<input type="date" id="end-date" name="end-date" value="<?php echo $event_dates['end-date']; ?>" required />
				</td>
			</tr>
		</table>
		<p>
			<?php
				echo '<em>'.__('For a one day event, do not set an end date.', 'events').'</em>';
			?>
		</p>
	</fieldset>
	
	<?php
}

/**
 * Save sidebar metabox.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_save_event_dates_metabox($post_id){

	if(! isset($_POST[ 'azrcrv-e-event-dates-nonce' ]) || ! wp_verify_nonce($_POST[ 'azrcrv-e-event-dates-nonce' ], basename(__FILE__))){
		return $post_id;
	}
	
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;
	
	if(! current_user_can('edit_post', $post_id)){
		return $post_id;
	}
	
	$post_type = get_post_type($post_id);
	
    if ($post_type == 'event'){
		update_post_meta($post_id, '_azrcrv_e_event_dates', array(
																	'start-date' => preg_replace("([^0-9-])", "", $_POST['start-date']),
																	'start-time' => preg_replace("([^0-9:])", "", $_POST['start-time']),
																	'end-time' => preg_replace("([^0-9:])", "", $_POST['end-time']),
																	'end-date' => preg_replace("([^0-9-])", "", $_POST['end-date']),
																),
						);
	}
	
	return esc_attr($_POST['autopost']);
}

/**
 * Add action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-e').'"><img src="'.plugins_url('/pluginmenu/images/Favicon-16x16.png', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'events').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_create_admin_menu(){
	
	// add settings to events submenu
	add_submenu_page(
						'edit.php?post_type=event'
						,esc_html__('Events Settings', 'events')
						,esc_html__('Settings', 'events')
						,'manage_options'
						,'azrcrv-e'
						,'azrcrv_e_display_options'
					);
	
	// add settings to azurecurve menu
	add_submenu_page(
						"azrcrv-plugin-menu"
						,esc_html__("Events Settings", "events")
						,esc_html__("Events", "events")
						,'manage_options'
						,'azrcrv-e'
						,'azrcrv_e_display_options'
					);
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'events'));
    }
	
	// Retrieve plugin configuration options from database
	$options = azrcrv_e_get_option('azrcrv-e');
	
	?>
	<div id="azrcrv-e-general" class="wrap azrcrv-e">
		<fieldset>
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php if(isset($_GET['settings-updated'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('Settings have been saved.', 'events'); ?></strong></p>
				</div>
			<?php } ?>
			
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="azrcrv_e_save_options" />
				<input name="page_options" type="hidden" value="widget-title" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field('azrcrv-e', 'azrcrv-e-nonce'); ?>
				
				<p>
					<?php printf(__('%s allows events to be created and displayed in a widget or using a shortcode.', 'events'), 'Events'); ?>
				</p>
				
				<p>
					
					<?php printf(__('The shortcode for displaying a single event is %s', 'events'), '<strong>[event slug="december-2021" width=100 height=100]</strong>'); ?>
				</p>
				
				<p>
					<?php printf(__('The shortcode for displaying multiple events is %s', 'events'), '<strong>[events category="webinars" width=150 height=150 limit=3]</strong>'); ?>
				</p>
				
				
				<table class="form-table">
					
					<tr>
						<th>
							<h3><?php _e('Widget', 'events'); ?></h3>
						</th>
					</tr>
					
					<tr>
						<th scope="row"><label for="widget-title">
							<?php esc_html_e('Title', 'events'); ?></label>
						</th>
						<td>
							<input name="widget-title" type="text" id="widget-title" value="<?php if (strlen($options['widget']['title']) > 0){ echo sanitize_text_field($options['widget']['title']); } ?>" class="regular-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="widget-intro-text">
							<?php esc_html_e('Intro Text', 'events'); ?></label>
						</th>
						<td>
							<input name="widget-intro-text" type="text" id="widget-intro-text" value="<?php if (strlen($options['widget']['intro-text']) > 0){ echo wp_kses($options['widget']['intro-text'], wp_kses_allowed_html()); } ?>" class="large-text" />
						</td>
					</tr>
							
					<tr>
						<th scope="row">
							<label for="widget-intro-text">
								<?php esc_html_e('Default Category', 'nearby'); ?>
							</label>
						</th>
						<td>
							<select name="widget-category">
								<?php
									$taxonomies = get_categories(
																	array(
																		'orderby' => 'name',
																		'hide_empty' => false,
																		'taxonomy' => 'event-categories',
																	)
																);
									
									foreach ($taxonomies as $taxonomy){
										if ($options['widget']['category'] == $taxonomy->slug){
											$selected = 'selected';
										}else{
											$selected = '';
										}
										echo '<option value="'.$taxonomy->slug.'" '.$selected.' >'.$taxonomy->name.'</option>';
									}
								?>
							</select>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="widget-width">
							<?php esc_html_e('Width', 'events'); ?></label>
						</th>
						<td>
							<input name="widget-width" type="number" min="1" id="widget-width" value="<?php if (strlen($options['widget']['width']) > 0){ echo sanitize_text_field($options['widget']['width']); } ?>" class="small-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="widget-height">
							<?php esc_html_e('Height', 'events'); ?></label>
						</th>
						<td>
							<input name="widget-height" type="number" min="1" id="widget-height" value="<?php if (strlen($options['widget']['height']) > 0){ echo sanitize_text_field($options['widget']['height']); } ?>" class="small-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="widget-limit">
							<?php esc_html_e('Limit', 'events'); ?></label>
						</th>
						<td>
							<input name="widget-limit" type="number" min="1" id="widget-limit" value="<?php if (strlen($options['widget']['limit']) > 0){ echo sanitize_text_field($options['widget']['limit']); } ?>" class="small-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="widget-date-format">
							<?php esc_html_e('Date Format', 'events'); ?></label>
						</th>
						<td>
							<input name="widget-date-format" type="text" id="widget-date-format" value="<?php if (strlen($options['widget']['date-format']) > 0){ echo sanitize_text_field($options['widget']['date-format']); } ?>" class="short-text" />
						</td>
					</tr>
					
					<tr>
						<th>
							<h3><?php _e('Shortcode', 'events'); ?></h3>
						</th>
					</tr>
							
					<tr>
						<th scope="row">
							<label for="shortcode-intro-text">
								<?php esc_html_e('Default Category', 'nearby'); ?>
							</label>
						</th>
						<td>
							<select name="shortcode-category">
								<?php
									$taxonomies = get_categories(
																	array(
																		'orderby' => 'name',
																		'hide_empty' => false,
																		'taxonomy' => 'event-categories',
																	)
																);
									
									foreach ($taxonomies as $taxonomy){
										if ($options['shortcode']['category'] == $taxonomy->slug){
											$selected = 'selected';
										}else{
											$selected = '';
										}
										echo '<option value="'.$taxonomy->slug.'" '.$selected.' >'.$taxonomy->name.'</option>';
									}
								?>
							</select>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="shortcode-width">
							<?php esc_html_e('Width', 'events'); ?></label>
						</th>
						<td>
							<input name="shortcode-width" type="number" min="1" id="shortcode-width" value="<?php if (strlen($options['shortcode']['width']) > 0){ echo sanitize_text_field($options['shortcode']['width']); } ?>" class="small-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="shortcode-height">
							<?php esc_html_e('Height', 'events'); ?></label>
						</th>
						<td>
							<input name="shortcode-height" type="number" min="1" id="shortcode-height" value="<?php if (strlen($options['shortcode']['height']) > 0){ echo sanitize_text_field($options['shortcode']['height']); } ?>" class="small-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="shortcode-limit">
							<?php esc_html_e('Limit', 'events'); ?></label>
						</th>
						<td>
							<input name="shortcode-limit" type="number" min="1" id="shortcode-limit" value="<?php if (strlen($options['shortcode']['limit']) > 0){ echo sanitize_text_field($options['shortcode']['limit']); } ?>" class="small-text" />
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="shortcode-date-format">
							<?php esc_html_e('Date Format', 'events'); ?></label>
						</th>
						<td>
							<input name="shortcode-date-format" type="text" id="shortcode-date-format" value="<?php if (strlen($options['shortcode']['date-format']) > 0){ echo sanitize_text_field($options['shortcode']['date-format']); } ?>" class="short-text" />
						</td>
					</tr>
				
				</table>
				
				<input type="submit" value="<? _e('Save Changes', 'events'); ?>" class="button-primary"/>
				
			</form>
		</fieldset>
	</div>
	<?php
}

/**
 * Save settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_save_options(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'nearby'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-e', 'azrcrv-e-nonce')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-e');
		
		$option_name = 'widget-title';
		if (isset($_POST[$option_name])){
			$options['widget']['title'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'widget-intro-text';
		if (isset($_POST[$option_name])){
			$options['widget']['intro-text'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'widget-category';
		if (isset($_POST[$option_name])){
			$options['widget']['category'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'widget-width';
		if (isset($_POST[$option_name])){
			$options['widget']['width'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'widget-height';
		if (isset($_POST[$option_name])){
			$options['widget']['height'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'widget-limit';
		if (isset($_POST[$option_name])){
			$options['widget']['limit'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'widget-date-format';
		if (isset($_POST[$option_name])){
			$options['widget']['date-format'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'shortcode-category';
		if (isset($_POST[$option_name])){
			$options['shortcode']['category'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'shortcode-width';
		if (isset($_POST[$option_name])){
			$options['shortcode']['width'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'shortcode-height';
		if (isset($_POST[$option_name])){
			$options['shortcode']['height'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'shortcode-limit';
		if (isset($_POST[$option_name])){
			$options['shortcode']['limit'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'shortcode-date-format';
		if (isset($_POST[$option_name])){
			$options['shortcode']['date-format'] = sanitize_text_field($_POST[$option_name]);
		}
		
		// Store updated options array to database
		update_option('azrcrv-e', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-e&settings-updated', admin_url('admin.php')));
		exit;
	}
}

/**
 * Register widget.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_create_widget(){
	register_widget('azrcrv_e_register_widget');
}

/**
 * Widget class.
 *
 * @since 1.0.0
 *
 */
class azrcrv_e_register_widget extends WP_Widget {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 */
	function __construct(){
		add_action('wp_enqueue_scripts', array($this, 'enqueue'));
		
		// Widget creation function
		parent::__construct('azrcrv-e',
							 'Events by azurecurve',
							 array('description' =>
									esc_html__('Events in a widget', 'events')));
	}
	
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue(){
		// Enqueue Styles
		wp_enqueue_style('azrcrv-e', plugins_url('assets/css/style.css', __FILE__), '', '1.0.0');
	}

	/**
	 * Display widget form in admin.
	 *
	 * @since 1.0.0
	 *
	 */
	function form($instance){
		
		$options = azrcrv_e_get_option('azrcrv-e');
		
		$title = (!empty($instance['title']) ? esc_attr($instance['title']) : $options['widget']['title']);
		
		$intro_text = (!empty($instance['intro-text']) ? esc_html($instance['intro-text']) : esc_html($options['widget']['intro-text']));
		
		$category = (!empty($instance['category']) ? esc_attr($instance['category']) : '');
		
		$width = (!empty($instance['width']) ? esc_attr($instance['width']) : $options['widget']['width']);
		
		$height = (!empty($instance['height']) ? esc_attr($instance['height']) : $options['widget']['height']);
		
		$limit = (!empty($instance['limit']) ? esc_attr($instance['limit']) : $options['widget']['limit']);
		
		$date_format = (!empty($instance['date-format']) ? esc_attr($instance['date-format']) : $options['widget']['date-format']);
		?>
		
		<p>
			<label for="<?php echo 
						$this->get_field_id('title'); ?>">
			<?php _e('Title:', 'events'); ?>&nbsp;			
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />			
			</label>
		</p> 
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('intro-text'); ?>">
			<?php _e('Intro Text:', 'events'); ?>&nbsp;			
			<input type="text" id="<?php echo $this->get_field_name('intro-text'); ?>" name="<?php echo $this->get_field_name('intro-text'); ?>" value="<?php echo $intro_text; ?>" />			
			</label>
		</p> 
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('category'); ?>">
			<?php _e('Category:', 'events');
			
			echo '&nbsp;';
			
			echo '<select id="'.$this->get_field_name('category').'" name="'.$this->get_field_name('category').'">';
				$taxonomies = get_categories(
												array(
													'orderby' => 'name',
													'hide_empty' => false,
													'taxonomy' => 'event-categories',
												)
											);
				
				foreach ($taxonomies as $taxonomy){
					if ($category == $taxonomy->slug){
						$selected = 'selected';
					}else{
						$selected = '';
					}
					echo '<option value="'.$taxonomy->slug.'" '.$selected.' >'.$taxonomy->name.'</option>';
				}
			echo	'</select>';
			?>	
			</label>
		</p>
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('width'); ?>">
			<?php _e('Width:', 'events'); ?>&nbsp;			
			<input type="number" id="<?php echo $this->get_field_name('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" class="small-text" value="<?php echo $width; ?>" />
			</label>
		</p>
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('height'); ?>">
			<?php _e('Height:', 'events'); ?>&nbsp;			
			<input type="number" id="<?php echo $this->get_field_name('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" class="small-text" value="<?php echo $height; ?>" />
			</label>
		</p>
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('limit'); ?>">
			<?php _e('Limit:', 'events'); ?>&nbsp;			
			<input type="number" id="<?php echo $this->get_field_name('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" class="small-text" value="<?php echo $limit; ?>" />
			</label>
		</p> 
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('date-format'); ?>">
			<?php _e('Date Format:', 'events'); ?>&nbsp;			
			<input type="text" id="<?php echo $this->get_field_name('date-format'); ?>" name="<?php echo $this->get_field_name('date-format'); ?>" value="<?php echo $date_format; ?>" />
			</label>
		</p> 

		<?php
	}

	/**
	 * Validate user input.
	 *
	 * @since 1.0.0
	 *
	 */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['intro-text'] = wp_kses($new_instance['intro-text'], wp_kses_allowed_html());
		$instance['category'] = sanitize_text_field($new_instance['category']);
		$instance['width'] = sanitize_text_field(intval($new_instance['width']));
		$instance['height'] = sanitize_text_field(intval($new_instance['height']));
		$instance['limit'] = sanitize_text_field(intval($new_instance['limit']));
		$instance['date-format'] = sanitize_text_field($new_instance['date-format']);

		return $instance;
	}
	
	/**
	 * Display widget on front end.
	 *
	 * @since 1.0.0
	 *
	 */
	function widget ($args, $instance){
	
		global $wpdb;
		
		$options = azrcrv_e_get_option('azrcrv-e');
		
		// Extract members of args array as individual variables
		extract($args);
		
		// display widget title
		echo $before_widget;
		echo $before_title;
		$title = (!empty($instance['title']) ? esc_attr($instance['title']) : $options['widget']['title']);
		echo apply_filters('widget_title', $title);
		echo $after_title;
		
		$intro_text = (!empty($instance['intro-text']) ? $instance['intro-text'] : '');
		$date_format = (!empty($instance['date-format']) ? esc_attr($instance['date-format']) : $options['widget']['date-format']);
		if (strlen($intro_text) > 0){
			echo '<p>'.wp_kses($intro_text, wp_kses_allowed_html()).'</p>';
		}
		
		$sql = azrcrv_e_create_upcoming_events_sql_statement($instance['category']);
		//echo $sql.'<p />';
		
		$events = $wpdb->get_results($sql);
		
		$count = 0;
		foreach ($events as $event){
			
			$year = date('Y');
			
			$event_details = get_post_meta($event->ID, '_azrcrv_e_event_dates', true);
			
			if (
					// before event end
					date_format(date_create($event_details['end-date']), "Y-m-d") >= date("Y-m-d")
				){
				
				$count += 1;
				
				echo '<div class="azrcrv-e-container-widget">';
					$title = $event->post_title;
					
					// display widget body
					if (has_post_thumbnail($event->ID)){
						$image = wp_get_attachment_image(get_post_thumbnail_id($event->ID), array($instance['width'],$instance['height']),'', array('class' => "img-responsive alignleft", 'alt' => get_the_title()));
						
						echo '<div class="azrcrv-e-widget-image">'.$image.'</div>';
					}
					
					echo '<div class="azrcrv-e-widget-details">';
						echo '<p><h3 class="azrcrv-e">'.$title.'</h3></p>';
						if ($event_details['start-date'] == $event_details['end-date']){
							$end_date = '';
						}else{
							$end_date = '-'.date_format(date_create($event_details['end-date']), $date_format);
						}
						echo '<p class="azrcrv-e-widget-dates">'.date_format(date_create($event_details['start-date']),$date_format).$end_date.' '.$event_details['start-time'].'-'.$event_details['end-time'].'</p>';
						echo '<p class="azrcrv-e-widget-excerpt">'.$event->post_excerpt.'</p>';
					echo '</div>';
				echo '</div>';
				echo '<p class="azrcrv-e-clear" />';
			
				if ($count == $instance['limit']){ break; }
			}
		}
		// display widget footer
		echo $after_widget;
	}
}

/**
 * create sql statement to select events by category.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_create_upcoming_events_sql_statement($category){
	
	global $wpdb;
		
	$sql = 
			"SELECT p.ID,p.post_title,p.post_content,p.post_excerpt FROM
				$wpdb->posts AS p
			INNER JOIN
				$wpdb->term_relationships AS tr
					on
						tr.object_id = p.ID
			INNER JOIN $wpdb->term_taxonomy AS tt
				ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN
				$wpdb->terms AS t
					ON
						t.term_id = tt.term_id
			WHERE
				t.slug = '%s'
			AND
				p.post_status = 'publish'
			AND
				p.post_type = 'event'
			ORDER BY
				p.post_date ASC";
	
	$sql = $wpdb->prepare($sql, $category);
	
	return $sql;
		
}

/**
 * display events shortcode.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_display_events($atts, $content = null){
	
	global $wpdb;
	
	$options = azrcrv_e_get_option('azrcrv-e');
	
	// get shortcode attributes
	$args = shortcode_atts(array(
		'category' => $options['shortcode']['category'],
		'width' => $options['shortcode']['width'],
		'height' => $options['shortcode']['height'],
		'limit' => $options['shortcode']['limit'],
	), $atts);
	$category = $args['category'];
	$width = $args['width'];
	$height = $args['height'];
	$limit = $args['limit'];
	
	$date_format = $options['shortcode']['date-format'];
	
	$sql = azrcrv_e_create_upcoming_events_sql_statement($category);
	//echo $sql.'<p />';
	
	$events = $wpdb->get_results( $sql );
	
	$output = '';
	$count = 0;
	foreach ($events as $event){
		
		$event_details = get_post_meta($event->ID, '_azrcrv_e_event_dates', true);
		
		if (date_format(date_create($event_details['end-date']), "Y-m-d") >= date("Y-m-d")){
			
			$count += 1;
			
			$output .= '<div class="azrcrv-e-container">';
			
				$title = $event->post_title;
				
				// display widget body
				if (has_post_thumbnail($event->ID)){
					$image = wp_get_attachment_image(get_post_thumbnail_id($event->ID), array($width,$height),'', array('class' => "img-responsive alignleft", 'alt' => get_the_title()));
					
					$output .= '<div class="azrcrv-e-image">'.$image.'</div>';
				}
				
				$output .= '<div class="azrcrv-e-details">';
					$output .= '<p><h3 class="azrcrv-e">'.$title.'</h3></p>';
					if ($event_details['start-date'] == $event_details['end-date']){
						$end_date = '';
					}else{
						$end_date = '-'.date_format(date_create($event_details['end-date']), $date_format);
					}
					$output .= '<p class="azrcrv-e-dates">'.date_format(date_create($event_details['start-date']),$date_format).$end_date.' '.$event_details['start-time'].'-'.$event_details['end-time'].'</p>';
					if (strlen($event->post_excerpt) > 0){
						$output .= '<p class="azrcrv-e-excerpt">'.$event->post_excerpt.'</p>';
					}
					if (strlen($event->post_content) > 0){
						$output .= wpautop($event->post_content);
					}
				$output .= '</div>';
			$output .= '</div>';
			$output .= '<p class="azrcrv-e-clear" />';
			
			if ($count == $limit){ break; }
		}
	}
	if (strlen($output) == 0){
		$output = sprintf(__('No events found for category %s', 'events'), '<em>'.$category.'</em>');
	}
	
	return $output;
}

/**
 * display event shortcode.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_display_event($atts, $content = null){
	
	global $wpdb;
	
	$options = azrcrv_e_get_option('azrcrv-e');
	
	// get shortcode attributes
	$args = shortcode_atts(array(
		'slug' => '',
		'width' => $options['shortcode']['width'],
		'height' => $options['shortcode']['height'],
	), $atts);
	$slug = $args['slug'];
	$width = $args['width'];
	$height = $args['height'];
	
	$date_format = $options['shortcode']['date-format'];
	
	$sql = azrcrv_e_create_upcoming_event_sql_statement($slug);
	//echo $sql.'<p />';
	
	$event = $wpdb->get_row( $sql );
		
	$event_details = get_post_meta($event->ID, '_azrcrv_e_event_dates', true);
	
	$output = '';
	if (date_format(date_create($event_details['end-date']), "Y-m-d") >= date("Y-m-d")){
		
		$output = '<div class="azrcrv-e-container">';
		
			$title = $event->post_title;
			
			// display widget body
			if (has_post_thumbnail($event->ID)){
				$image = wp_get_attachment_image(get_post_thumbnail_id($event->ID), array($width,$height),'', array('class' => "img-responsive alignleft", 'alt' => get_the_title()));
				
				$output .= '<div class="azrcrv-e-image">'.$image.'</div>';
			}
			
			$output .= '<div class="azrcrv-e-details">';
				$output .= '<p><h3 class="azrcrv-e">'.$title.'</h3></p>';
				if ($event_details['start-date'] == $event_details['end-date']){
					$end_date = '';
				}else{
					$end_date = '-'.date_format(date_create($event_details['end-date']), $date_format);
				}
				$output .= '<p class="azrcrv-e-dates">'.date_format(date_create($event_details['start-date']),$date_format).$end_date.' '.$event_details['start-time'].'-'.$event_details['end-time'].'</p>';
				if (strlen($event->post_excerpt) > 0){
					$output .= '<p class="azrcrv-e-excerpt">'.$event->post_excerpt.'</p>';
				}
				if (strlen($event->post_content) > 0){
					$output .= wpautop($event->post_content);
				}
			$output .= '</div>';
		$output .= '</div>';
		$output .= '<p class="azrcrv-e-clear" />';
	}else{
		$output = sprintf(__('Event %s found.', 'events'), '<em>'.$slug.'</em>');
	}
	
	return $output;
}

/**
 * create sql statement to select single events by slug.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_create_upcoming_event_sql_statement($slug){
	
	global $wpdb;
		
	$sql = 
			"SELECT p.ID,p.post_title,p.post_content,p.post_excerpt FROM
				$wpdb->posts AS p
			WHERE
				p.post_name = '%s'
			AND
				p.post_status = 'publish'
			AND
				p.post_type = 'event'
			";
	
	$sql = $wpdb->prepare($sql, $slug);
	
	return $sql;
		
}