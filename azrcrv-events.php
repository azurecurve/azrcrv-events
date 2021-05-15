<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Events
 * Description: Announce holidays, events, achievements and notable historical figures in a widget.
 * Version: 1.3.0
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
add_action('add_meta_boxes', 'azrcrv_e_create_tweet_metabox');
add_action('save_post', 'azrcrv_e_save_tweet_metabox', 11, 2);
add_action('add_meta_boxes', 'azrcrv_e_create_tweet_history_metabox');
add_action('admin_menu', 'azrcrv_e_add_to_twitter_sidebar_metabox');
add_action('save_post', 'azrcrv_e_save_to_twitter_sidebar_metabox', 10, 1);
add_action('wp_insert_post', 'azrcrv_e_check_tweet', 12, 2);
add_action('azrcrv_e_cron_tweet_event', 'azrcrv_e_perform_tweet_event', 10, 2);
add_action('transition_post_status', 'azrcrv_e_post_status_transition', 13, 3);
add_action('admin_enqueue_scripts', 'azrcrv_e_load_jquery');
add_action('admin_enqueue_scripts', 'azrcrv_e_media_uploader');
add_action('admin_enqueue_scripts', 'azrcrv_e_load_admin_style');

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
 * Load media uploaded.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_load_jquery(){
	wp_enqueue_script('azrcrv-e-jquery', plugins_url('assets/jquery/jquery.js', __FILE__), array('jquery'));
}

/**
 * Load media uploaded.
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_media_uploader(){
	global $post_type;
	
	if(function_exists('wp_enqueue_media')){
		wp_enqueue_media();
	}else{
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
	}
}

/**
 * Load admin css.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_load_admin_style(){
    wp_register_style('azrcrv-e-admin-css', plugins_url('assets/css/admin.css', __FILE__), false, '1.0.0');
    wp_enqueue_style( 'azrcrv-e-admin-css' );
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
											'hide' => 0,
										),
						'shortcode' => array(
												'category' => '',
												'width' => 300,
												'height' => 300,
												'limit' => 10,
												'date-format' => 'm/d/Y',
											),
						'to-twitter' => array(
												'integrate' => 0,
												'tweet' => 0,
												'retweet' => 0,
												'retweet-prefix' => 'ICYMI:',
												'tweet-format' => '%t %h',
												'tweet-days-before' => 14,
												'tweet-time' => '10:00',
												'retweet-days-before' => 7,
												'retweet-time' => '16:00',
												'use-featured-image' => 1,
											),
					);

	$options = get_option($option_name, $defaults);

	$options = azrcrv_e_recursive_parse_args($options, $defaults);

	return $options;

}

/**
 * Recursively parse options to merge with defaults.
 *
 * @since 1.1.0
 *
 */
function azrcrv_e_recursive_parse_args( $args, $defaults ) {
	$new_args = (array) $defaults;

	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
			$new_args[ $key ] = azrcrv_e_recursive_parse_args( $value, $new_args[ $key ] );
		}
		else {
			$new_args[ $key ] = $value;
		}
	}

	return $new_args;
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
						'label' => esc_html__( 'Categories' ),
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
		return esc_html__('Event Outline', 'events');
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
					<?php esc_html_e('Start Date: ', 'events'); ?>
				</td>
				<td>
					<input type="date" id="start-date" name="start-date" value="<?php echo $event_dates['start-date']; ?>" required />
				</td>
			</tr>
			
			<tr>
				<td>
					<?php esc_html_e('Start Time: ', 'events'); ?>
				</td>
				<td>
					<input type="time" id="start-time" name="start-time" value="<?php echo $event_dates['start-time']; ?>" required />
				</td>
			</tr>
		
			<tr>
				<td>
					<?php esc_html_e('End Time:', 'events'); ?>
				</td>
				<td>
					<input type="time" id="end-time" name="end-time" value="<?php echo $event_dates['end-time']; ?>" required />
				</td>
			</tr>
		
			<tr>
				<td>
					<?php esc_html_e('End Date:', 'events'); ?>
				</td>
				<td>
					<input type="date" id="end-date" name="end-date" value="<?php echo $event_dates['end-date']; ?>" required />
				</td>
			</tr>
		</table>
		<p>
			<?php
				echo '<em>'.esc_html__('For a one day event, do not set an end date.', 'events').'</em>';
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
 * Create the post tweet metabox
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_create_tweet_metabox() {
	
	$to_twitter_enabled = azrcrv_e_is_plugin_active('azrcrv-to-twitter/azrcrv-to-twitter.php');
	
	if ($to_twitter_enabled){
		
		$options = azrcrv_e_get_option('azrcrv-e');
		
		if ($options['to-twitter']['integrate'] == 1){
			add_meta_box(
				'azrcrv_e_tweet_metabox', // Metabox ID
				'Tweet', // Title to display
				'azrcrv_e_render_tweet_metabox', // Function to call that contains the metabox content
				'event', // Post type to display metabox on
				'normal', // Where to put it (normal = main colum, side = sidebar, etc.)
				'default' // Priority relative to other metaboxes
			);
		}
	}
}

/**
 * Render the post tweet metabox markup
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_render_tweet_metabox() {
	// Variables
	global $post; // Get the current post data
	$post_tweet = get_post_meta($post->ID, '_azrcrv_e_post_tweet', true); // Get the saved values
	$post_media = get_post_meta($post->ID, '_azrcrv_e_post_tweet_media', true); // Get the saved values
	
	?>

		<fieldset>
			<div>
				<table style="width: 100%; border-collapse: collapse;">
					<tr>
						<td style="width: 100%;">
							<p>
								<input
									type="text"
									name="post_tweet"
									id="post_tweet"
									class="large-text"
									value="<?php echo esc_attr($post_tweet); ?>"
								>
							</p>
							<p>
								<?php printf(esc_html__('To regenerate tweet blank the field and update post.', 'events'), '%s'); ?>
							</p>
		
							<p>
								<?php
									$no_image = plugin_dir_url(__FILE__).'assets/images/no-image.svg';
									$tweet_media = array();
									for ($media_loop = 0; $media_loop <= 3; $media_loop++){
										if (isset($post_media[$media_loop])){
											$tweet_media[$media_loop] = array(
																				'image' => $post_media[$media_loop],
																				'value' => $post_media[$media_loop],
																			);
										}else{
											$tweet_media[$media_loop] = array(
																				'image' => $no_image,
																				'value' => '',
																			);
										}
									}
								?>
								
								<p style="clear: both; " />
								
								<div style="width: 100%; display: block; ">
									<div style="width: 100%; display: block; padding-bottom: 12px; ">
										<?php esc_html_e('Select up to four images to include with tweet; if the <em>Use Featured Image</em> option is marked and a featured image set, only the first three media images from below will be used.', 'events'); ?>
									</div>
									<?php
										foreach ($tweet_media AS $media_key => $media){
											$key = $media_key + 1;
											echo '<div style="float: left; width: 170px; text-align: center; ">';
												echo '<img src="'.$media['image'].'" id="tweet-image-'.$key.'" style="width: 160px;"><br />';
												echo '<input type="hidden" name="tweet-selected-image-'.$key.'" id="tweet-selected-image-'.$key.'" value="'.$media['value'].'" class="regular-text" />';
												echo '<input type="button" id="azrcrv-e-upload-image-'.$key.'" class="button upload" value="'.esc_html__('Upload', 'events').'" />&nbsp;';
												echo '<input type="button" id="azrcrv-e-remove-image-'.$key.'" class="button remove" value="'.esc_html__( 'Remove', 'events').'" />';
											echo '</div>';
										}
									?>
								</div>
								
								<p style="clear: both; padding-bottom: 6px; " />
							</p>
						<td>
					</tr>
				</table>
			</div>
		</fieldset>

	<?php
	// Security field
	// This validates that submission came from the
	// actual dashboard and not the front end or
	// a remote server.
	wp_nonce_field('azrcrv_e_form_tweet_metabox_nonce', 'azrcrv_e_form_tweet_metabox_process');
}

/**
 * Save the post tweet metabox
 * @param  Number $post_id The post ID
 * @param  Array  $post    The post data
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_save_tweet_metabox( $post_id, $post ) {

	// Verify that our security field exists. If not, bail.
	if ( !isset( $_POST['azrcrv_e_form_tweet_metabox_process'] ) ) return;

	// Verify data came from edit/dashboard screen
	if ( !wp_verify_nonce( $_POST['azrcrv_e_form_tweet_metabox_process'], 'azrcrv_e_form_tweet_metabox_nonce' ) ) {
		return $post->ID;
	}

	// Verify user has permission to edit post
	if ( !current_user_can( 'edit_post', $post->ID )) {
		return $post->ID;
	}
	
	$tt_options = azrcrv_tt_get_option('azrcrv-tt');
	$options = azrcrv_e_get_option('azrcrv-e');
	
	if (strlen($_POST['post_tweet']) == 0){
		
		$autopost_tweet = get_post_meta($post->ID, '_azrcrv_e_tweet', true);
		$hashtags_string = $autopost_tweet['hashtags'];
		
		$tweet = $post->post_title;
		
		$post_tweet = $options['to-twitter']['tweet-format'];
		
		if (!isset($post_tweet)||$post_tweet == ''){
			$post_tweet = '%t %h';
		}
		
		$post_tweet = str_replace('%t', $tweet, $post_tweet);
		$post_tweet = str_replace('%h', $hashtags_string, $post_tweet);
		
		if ($tt_options['prefix_tweets_with_dot'] == 1){
			if (substr($post_tweet, 0, 1) == '@'){
				$post_tweet = '.'.$post_tweet;
			}
		}
	}else{
		/**
		 * Sanitize the submitted data
		 */
		$post_tweet = sanitize_text_field( $_POST['post_tweet'] );
	}
	
	$media = array();
	for ($media_loop = 1; $media_loop <= 4; $media_loop++){
		if(strlen($_POST['tweet-selected-image-'.$media_loop]) >= 1){
			$media[] = $_POST['tweet-selected-image-'.$media_loop];
		}
	}
	
	// Save our submissions to the database
	update_post_meta($post->ID, '_azrcrv_e_post_tweet', $post_tweet);
	update_post_meta($post->ID, '_azrcrv_e_post_tweet_media', $media);

}

/**
 * Create the post tweet history metabox
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_create_tweet_history_metabox() {
	
	global $post; // Get the current post data
	
	$to_twitter_enabled = azrcrv_e_is_plugin_active('azrcrv-to-twitter/azrcrv-to-twitter.php');
	
	if ($to_twitter_enabled){
		if(metadata_exists('post', $post->ID, '_azrcrv_tt_tweet_history')) {
		
			$options = azrcrv_e_get_option('azrcrv-e');
			
			if ($options['to-twitter']['integrate'] == 1){
				add_meta_box(
					'azrcrv_e_tweet_history_metabox', // Metabox ID
					'Tweet History', // Title to display
					'azrcrv_e_render_tweet_history_metabox', // Function to call that contains the metabox content
					'event', // Post type to display metabox on
					'normal', // Where to put it (normal = main colum, side = sidebar, etc.)
					'default' // Priority relative to other metaboxes
				);
			}
		}
	}
}

/**
 * Render the post tweet history metabox markup
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_render_tweet_history_metabox() {
	// Variables
	global $post; // Get the current post data
	
	?>

		<fieldset>
			<div>
				<table style="width: 100%; border-collapse: collapse;">
					<tr>
						<td style="width: 100%;">
							<p>
							<?php
							if(metadata_exists('post', $post->ID, '_azrcrv_tt_tweet_history')) {
								echo '<strong>'.esc_html__('Previous Tweets', 'events').'</strong><br />';
								foreach(array_reverse(get_post_meta($post->ID, '_azrcrv_tt_tweet_history', true )) as $key => $tweet){
									if (is_array($tweet)){ $tweet_detail = $tweet['tweet']; }else{ $tweet_detail = $tweet; }
									
									if (isset($tweet['key'])){ $tweet_date = $tweet['key']; }else{ $tweet_date = strtotime($key); }
									$tweet_date = date('d/m/Y H:i', $tweet_date);
									
									if ($tweet['status'] == ''){
										$status = '';
									}elseif ($tweet['status'] == 200){
										$status = ' '.$tweet['status'].' ';
									}else{
										$status = ' <span style="color: red; font-weight:900;">'.$tweet['status'].'</span> ';
									}
									
									if (isset($tweet['author']) AND strlen($tweet['author']) > 0){
										$tweet_link = '<a href="https://twitter.com/'.$tweet['author'].'/status/'.$tweet['tweet_id'].'" style="text-decoration: none; "><span class="dashicons dashicons-twitter"></span></a>&nbsp';
									}else{
										$tweet_link = '';
									}
									
									echo '•&nbsp;'.$tweet_date.' - '.$status.' - <em>'.$tweet_link.$tweet_detail.'</em><br />';
								}	
							}
							?>
							</p>
						<td>
					</tr>
				</table>
			</div>
		</fieldset>

	<?php
}

/**
 * Add post metabox to sidebar.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_add_to_twitter_sidebar_metabox(){
	
	$to_twitter_enabled = azrcrv_e_is_plugin_active('azrcrv-to-twitter/azrcrv-to-twitter.php');
	
	if ($to_twitter_enabled){
		
		$options = azrcrv_e_get_option('azrcrv-e');
		
		if ($options['to-twitter']['integrate'] == 1){
			add_meta_box('azrcrv-e-to-twitter-box', esc_html__('Autopost Tweet', 'events'), 'azrcrv_e_generate_to_twitter_sidebar_metabox', 'event', 'side', 'default');
		}
		
	}
}

/**
 * Generate post sidebar metabox.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_generate_to_twitter_sidebar_metabox(){
	
	global $post;
	
	$options = azrcrv_e_get_option('azrcrv-e');
	
	$autopost_tweet = get_post_meta($post->ID, '_azrcrv_e_tweet', true);
	
	if (is_array($autopost_tweet)){
		$use_featured_image = $autopost_tweet['use-featured-image'];
		$tweet = $autopost_tweet['tweet'];
		$tweet_days_before = $autopost_tweet['tweet-days-before'];
		$tweet_time = $autopost_tweet['tweet-time'];
		$retweet = $autopost_tweet['retweet'];
		$retweet_days_before = $autopost_tweet['retweet-days-before'];
		$retweet_time = $autopost_tweet['retweet-time'];
		$hashtags = $autopost_tweet['hashtags'];
	}else{
		$use_featured_image = $options['to-twitter']['use-featured-image'];
		$tweet = $options['to-twitter']['tweet'];
		$retweet = $options['to-twitter']['retweet'];
		$tweet_days_before = $options['to-twitter']['tweet-days-before'];
		$tweet_time = $options['to-twitter']['tweet-time'];
		$retweet_days_before = $options['to-twitter']['retweet-days-before'];
		$retweet_time = $options['to-twitter']['retweet-time'];
		$hashtags = '';
	}
	
	echo '<p class="azrcrv-e-tweet">';
		wp_nonce_field(basename(__FILE__), 'azrcrv-e-to-twitter-sidebar-nonce');
		
		if ($use_featured_image == 1){
			$checked = 'checked="checked"';
		}else{
			$checked = '';
		}
		echo '<p>
				<label>
					<input type="checkbox" name="use-featured-image" '.$checked.' />  '.esc_html__('Use featured image as tweet media image 1?', 'events').'
				</label>';
		echo '</p>';
		
		if ($tweet == 1){
			$checked = 'checked="checked"';
		}else{
			$checked = '';
		}
		echo '<p><label><input type="checkbox" name="tweet" '.$checked.' />  '.esc_html__('Tweet event?', 'events').'</label></p>
		
		<p>
			'.esc_html__('Tweet Days Before: ', 'events').'
			
			<input type="number" min=1 step=1 id="tweet-days-before" name="tweet-days-before" value="'.esc_html($tweet_days_before).'" required class="small-text" />
		</p>
		
		<p>
			'.esc_html__('Tweet Time: ', 'events').'
			
			<input type="time" id="tweet-time" name="tweet-time" value="'.esc_html($tweet_time).'" required />
		</p>';
		
		if ($retweet == 1){
			$checked = 'checked="checked"';
		}else{
			$checked = '';
		}
		echo '<p><label><input type="checkbox" name="retweet" '.$checked.' />  '.esc_html__('Retweet event?', 'events').'</label></p>
		
		<p>
			'.esc_html__('Retweet Days Before: ', 'events').'
			
			<input type="number" min=0 step=1 id="retweet-days-before" name="retweet-days-before" value="'.esc_html($retweet_days_before).'" required class="small-text" />
		</p>
		
		<p>
			'.esc_html__('Retweet Time: ', 'events').'
			
			<input type="time" id="retweet-time" name="retweet-time" value="'.esc_html($retweet_time).'" required />
		</p>';
		
		echo '<p>
			<label for="hashtags">Hashtags</label><br/>
			<input name="hashtags" type="text" style="width: 100%;" value="'.esc_html($hashtags).'" />
		</p>
	</p>';
	
}

/**
 * Save To Twitter Sidebar Metabox.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_save_to_twitter_sidebar_metabox($post_id){
	
	if (! isset($_POST['azrcrv-e-to-twitter-sidebar-nonce']) || ! wp_verify_nonce($_POST['azrcrv-e-to-twitter-sidebar-nonce'], basename(__FILE__))){
		return $post_id;
	}
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return $post_id;
	}
	
	if (! current_user_can('edit_post', $post_id)){
		return $post_id;
	}
	
	$post_type = get_post_type($post_id);
	
    if ($post_type == 'event'){
		if (isset($_POST['use-featured-image'])){
			$use_featured_image = 1;
		}else{
			$use_featured_image = 0;
		}
		if (isset($_POST['tweet'])){
			$tweet = 1;
		}else{
			$tweet = 0;
		}
		
		$tweet_days_before = sanitize_text_field(intval($_POST['tweet-days-before']));
		
		$tweet_time = preg_replace("([^0-9-:-])", "", $_POST['tweet-time']);
		
		if (isset($_POST['retweet'])){
			$retweet = 1;
		}else{
			$retweet = 0;
		}
		
		$retweet_days_before = sanitize_text_field(intval($_POST['retweet-days-before']));
		
		$retweet_time = preg_replace("([^0-9-:-])", "", $_POST['retweet-time']);
		
		$hashtags = sanitize_text_field($_POST['hashtags']);
		
		$autopost_tweet = get_post_meta($post_id, '_azrcrv_e_tweet', true);
		
		if (!is_array($autopost_tweet)){
			$autopost_tweet = array(
										'tweeted-date' => '1900-01-01',
										'retweeted-date' => '1900-01-01',
									);
		}
		
		$autopost_tweet['use-featured-image'] = $use_featured_image;
		$autopost_tweet['tweet'] = $tweet;
		$autopost_tweet['tweet-days-before'] = $tweet_days_before;
		$autopost_tweet['tweet-time'] = $tweet_time;
		$autopost_tweet['retweet'] = $retweet;
		$autopost_tweet['retweet-days-before'] = $retweet_days_before;
		$autopost_tweet['retweet-time'] = $retweet_time;
		$autopost_tweet['hashtags'] = $hashtags;
		
		update_post_meta($post_id, '_azrcrv_e_tweet', $autopost_tweet);
	}
	
	return;
}

/**
 * Perform tweet for event.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_perform_tweet_event($cron_type, $post_id){
	
	$post = get_post($post_id);
	if ($post->post_status != 'publish'){ return; }
	
	$autopost_tweet = get_post_meta($post_id, '_azrcrv_e_tweet', true);
	if ($cron_type == 'tweet'){
		$autopost_tweet['tweeted-date'] = date('Y-m-d');
	}
	if ($cron_type == 'retweet'){
		$autopost_tweet['retweeted-date'] = date('Y-m-d');
	}
	update_post_meta($post_id, '_azrcrv_e_tweet', $autopost_tweet);

	$post_tweet = get_post_meta($post_id, '_azrcrv_e_post_tweet', true);
	$media_to_use = array();
	if ($autopost_tweet['use-featured-image'] == 1 AND has_post_thumbnail($post_id)){
		$post_image = get_the_post_thumbnail_url($post_id, 'full'); ;
		$media_to_use[] = $post_image;
	}
	$post_media = get_post_meta( $post_id, '_azrcrv_e_post_tweet_media', true ); // get tweet content
	
	$options = azrcrv_e_get_option('azrcrv-e');
	
	if ($cron_type == 'retweet'){
		$prefix = $options['to-twitter']['retweet-prefix'];
		if (strlen($prefix) > 0){
			$prefix .= ' ';
		}
	}else{
		$prefix = '';
	}
	
	$post_tweet = $prefix.$post_tweet; //text for your tweet.
	
	$parameters = array("status" => $post_tweet);
	if (isset($post_media) AND is_array($post_media)){
		$media_pos = 0;
		foreach ($post_media as $media){
			$media_pos++;
			if ($media_pos == 4 AND isset($post_image)){
				break;
			}else{
				$media_to_use[] = $media;
			}
		}
		$parameters['media-urls'] = $media_to_use;
	}else{
		if (isset($post_image)){
			$parameters['media-urls'] = $media_to_use;
		}
	}
	
	$tweet_result = azrcrv_tt_post_tweet($parameters);
	
	$tt_options = azrcrv_tt_get_option('azrcrv-tt');
	
	if ($tt_options['record_tweet_history'] == 1){

		$tweet_history = get_post_meta($post_id, '_azrcrv_tt_tweet_history', true);
		if (!is_array($tweet_history)){ $tweet_history = array(); }
		$tweet_history[] = array(
									'key' => time(),
									'date' => date("Y-m-d"),
									'time' => date("H:i"),
									'tweet_id' => $tweet_result['id'],
									'author' => $tweet_result['screen_name'],
									'tweet' => $post_tweet,
									'status' => $tweet_result['status'],
								);
		update_post_meta($post_id, '_azrcrv_tt_tweet_history', $tweet_history);
	}
	
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
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-e').'"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'events').'</a>';
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
	
	$to_twitter_enabled = azrcrv_e_is_plugin_active('azrcrv-to-twitter/azrcrv-to-twitter.php');
	
	?>
	<div id="azrcrv-e-general" class="wrap azrcrv-e">
		<fieldset>
			<h1>
				<?php
					echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
					esc_html_e(get_admin_page_title());
				?>
			</h1>
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
					<?php printf(esc_html__('%s allows events to be created and displayed in a widget or using a shortcode.', 'events'), 'Events'); ?>
				</p>
				
				<p>
					
					<?php printf(esc_html__('The shortcode for displaying a single event is %s', 'events'), '<strong>[event slug="december-2021" width=100 height=100]</strong>'); ?>
				</p>
				
				<p>
					<?php printf(esc_html__('The shortcode for displaying multiple events is %s', 'events'), '<strong>[events category="webinars" width=150 height=150 limit=3]</strong>'); ?>
				</p>
				
				<?php
					if(isset($_GET['i'])){
						$tab1active = '';
						$tab3active = 'nav-tab-active';
						$tab1visibility = 'invisible';
						$tab3visibility = '';
					}else{
						$tab1active = 'nav-tab-active';
						$tab3active = '';
						$tab1visibility = '';
						$tab3visibility = 'invisible';
					}
				?>
				<h2 class="nav-tab-wrapper nav-tab-wrapper-azrcrv-e">
					<a class="nav-tab <?php echo $tab1active; ?>" data-item=".tabs-1" href="#tabs-1"><?php esc_html_e('Widget', 'events') ?></a>
					<a class="nav-tab" data-item=".tabs-2" href="#tabs-2"><?php esc_html_e('Shortcode', 'events') ?></a>
					<a class="nav-tab <?php echo $tab3active; ?>" data-item=".tabs-3" href="#tabs-3"><?php esc_html_e('To Twitter Integration', 'events') ?></a>
				</h2>
				<div>
					<div class="azrcrv_e_tabs tabs-1 <?php echo $tab1visibility; ?>">
				
						<table class="form-table">
							
							<tr>
								<th>
									<h3><?php esc_html_e('Widget', 'events'); ?></h3>
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
										<?php esc_html_e('Default Category', 'events'); ?>
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
								<th scope="row">
									<label for="widget-hide">
										<?php esc_html_e('Hide widget?', 'events'); ?>
									</label>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span>
												<?php esc_html_e('Hide widget when no events found?', 'events'); ?>
											</span>
										</legend>
										<label for="widget-hide">
											<input name="widget-hide" type="checkbox" id="widget-hide" value="1" <?php checked('1', $options['widget']['hide']); ?> />
											<?php esc_html_e('Hide widget when no events found.', 'events'); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>
					
					<div class="azrcrv_e_tabs tabs-2 invisible">
				
						<table class="form-table">
							
							<tr>
								<th>
									<h3><?php esc_html_e('Shortcode', 'events'); ?></h3>
								</th>
							</tr>
									
							<tr>
								<th scope="row">
									<label for="shortcode-intro-text">
										<?php esc_html_e('Default Category', 'events'); ?>
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
					</div>
				
					<div class="azrcrv_e_tabs <?php echo $tab3visibility; ?> tabs-3">
				
						<table class="form-table">
								
							<tr>
								<th scope="row">
									<label for="to-twitter-integration">
										<?php esc_html_e('Enable integration', 'widget-announcements'); ?>
									</label>
								</th>
								<td>
									<?php
										if ($to_twitter_enabled){ ?>
											<label for="to-twitter-integration"><input name="to-twitter-integration" type="checkbox" id="to-twitter-integration" value="1" <?php checked('1', $options['to-twitter']['integrate']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'widget-announcements'), '<a href="admin.php?page=azrcrv-tt">To Twitter</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?></label>
										<?php }else{
											printf(esc_html__('%s from %s not installed/activated.', 'widget-announcements'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/">To Twitter</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
										}
									?>
								</td>
							</tr>
							
							<? if ($to_twitter_enabled AND $options['to-twitter']['integrate'] == 1){ ?>
					
								<tr>
									<th scope="row">
										<label for="to-twitter-tweet">
											<?php esc_html_e('Tweet', 'events'); ?>
										</label>
									</th>
									<td>
										<label for="to-twitter-tweet"><input name="to-twitter-tweet" type="checkbox" id="to-twitter-tweet" value="1" <?php checked('1', $options['to-twitter']['tweet']); ?> /><?php esc_html_e('Send tweet at below time?', 'events'); ?></label>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="to-twitter-tweet-days-before">
											<?php esc_html_e('Tweet <em>n</em> Days Before', 'events'); ?>
										</label>
									</th>
									<td>										
										<input type="number" id="to-twitter-tweet-days-before" name="to-twitter-tweet-days-before" value="<?php esc_html_e($options['to-twitter']['tweet-days-before']); ?>" required class="small-text" />
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="to-twitter-tweet-time">
											<?php esc_html_e('Tweet Time', 'events'); ?>
										</label>
									</th>
									<td>										
										<input type="time" id="to-twitter-tweet-time" name="to-twitter-tweet-time" value="<?php esc_html_e($options['to-twitter']['tweet-time']); ?>" required />
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="to-twitter-retweet">
											<?php esc_html_e('Reweet', 'events'); ?>
										</label>
									</th>
									<td>
										<label for="to-twitter-retweet"><input name="to-twitter-retweet" type="checkbox" id="to-twitter-retweet" value="1" <?php checked('1', $options['to-twitter']['retweet']); ?> /><?php esc_html_e('Send retweet at below time?', 'events'); ?></label>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="to-twitter-retweet-days-before">
											<?php esc_html_e('Retweet <em>n</em> Days Before', 'events'); ?>
										</label>
									</th>
									<td>										
										<input type="number" id="to-twitter-retweet-days-before" name="to-twitter-retweet-days-before" value="<?php esc_html_e($options['to-twitter']['retweet-days-before']); ?>" required class="small-text" />
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="to-twitter-retweet-time">
											<?php esc_html_e('Tweet Time', 'events'); ?>
										</label>
									</th>
									<td>										
										<input type="time" id="to-twitter-retweet-time" name="to-twitter-retweet-time" value="<?php esc_html_e($options['to-twitter']['retweet-time']); ?>" required />
									</td>
								</tr>
								
								<tr>
									<th scope="row"><label for="to-twitter-retweet-prefix">
										<?php esc_html_e('Retweet Prefix', 'events'); ?></label>
									</th>
									<td>
										<input name="to-twitter-retweet-prefix" type="text" id="to-twitter-retweet-prefix" value="<?php if (strlen($options['to-twitter']['retweet-prefix']) > 0){ echo sanitize_text_field($options['to-twitter']['retweet-prefix']); } ?>" class="regular-text" />
									</td>
								</tr>
								
								<tr>
									<th scope="row"><label for="to-twitter-tweet-format">
										<?php esc_html_e('Tweet Format', 'events'); ?></label>
									</th>
									<td>
										<input name="to-twitter-tweet-format" type="text" id="to-twitter-tweet-format" value="<?php if (strlen($options['to-twitter']['tweet-format']) > 0){ echo sanitize_text_field($options['to-twitter']['tweet-format']); } ?>" class="regular-text" />
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="to-twitter-use-featured-image">
											<?php esc_html_e('Use Featured Imge', 'events'); ?>
										</label>
									</th>
									<td>
										<label for="to-twitter-use-featured-image"><input name="to-twitter-use-featured-image" type="checkbox" id="to-twitter-use-featured-image" value="1" <?php checked('1', $options['to-twitter']['use-featured-image']); ?> /><?php esc_html__('Use featured image? Only three other media images can be included in the tweet.', 'events'); ?></label>
									</td>
								</tr>
							
							<?php } ?>
							
						</table>
					</div>
				</div>
				
				<input type="submit" value="<? esc_html_e('Save Changes', 'events'); ?>" class="button-primary"/>
				
			</form>
		</fieldset>
	</div>
	
	<div>
		<p>
		&nbsp;
		</p>
		<p>
			<label for="additional-plugins">
				<?php printf(esc_html__('This plugin integrates with the following plugins from %s:', 'events'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
			</label>
			<ul class='azrcrv-plugin-index'>
				<li>
					<?php
					if ($to_twitter_enabled){
						echo '<a href="admin.php?page=azrcrv-tt" class="azrcrv-plugin-index">To Twitter</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/" class="azrcrv-plugin-index">To Twitter</a>';
					}
					?>
				</li>
			</ul>
		</p>
	</div>
	<?php
}

/**
 * Check if other plugin active.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_is_plugin_active($plugin){
    return in_array($plugin, (array) get_option('active_plugins', array()));
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
		wp_die(esc_html__('You do not have permissions to perform this action', 'events'));
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
		
		$option_name = 'widget-hide';
		if (isset($_POST[$option_name])){
			$options['widget']['hide'] = 1;
		}else{
			$options['widget']['hide'] = 0;
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
		
		$option_name = 'to-twitter-integration';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['integrate'] = 1;
		}else{
			$options['to-twitter']['integrate'] = 0;
		}
		
		$option_name = 'to-twitter-tweet';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['tweet'] = 1;
		}else{
			$options['to-twitter']['tweet'] = 0;
		}
		
		$option_name = 'to-twitter-tweet-days-before';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['tweet-days-before'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'to-twitter-tweet-time';
		if (isset($_POST[$option_name])){
			$tweet_time = preg_replace("([^0-9-:-])", "", $_POST[$option_name]);
			$options['to-twitter']['tweet-time'] = sanitize_text_field($tweet_time);
		}
		
		$option_name = 'to-twitter-retweet';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['retweet'] = 1;
		}else{
			$options['to-twitter']['retweet'] = 0;
		}
		
		$option_name = 'to-twitter-retweet-days-before';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['retweet-days-before'] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'to-twitter-retweet-time';
		if (isset($_POST[$option_name])){
			$retweet_time = preg_replace("([^0-9-:-])", "", $_POST[$option_name]);
			$options['to-twitter']['retweet-time'] = sanitize_text_field($retweet_time);
		}
		
		$option_name = 'to-twitter-retweet-prefix';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['retweet-prefix'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'to-twitter-tweet-format';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['tweet-format'] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'to-twitter-use-featured-image';
		if (isset($_POST[$option_name])){
			$options['to-twitter']['use-featured-image'] = 1;
		}else{
			$options['to-twitter']['use-featured-image'] = 0;
		}
		
		// Store updated options array to database
		update_option('azrcrv-e', $options);
		
		$response = '';
		if ($original_options['to-twitter']['integrate'] == 0 AND $options['to-twitter']['integrate'] == 1){
			$response = '&i';
		}
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-e&settings-updated'.$response, admin_url('admin.php')));
		exit;
	}
}

/**
 * Clear Cron for event.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_create_cron_single($tweet_time, $cron_name, $cron_type, $post_id){
	
	wp_schedule_single_event($tweet_time, $cron_name, array($cron_type, $post_id));
	
}

/**
 * Clear Cron for event.
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_clear_cron_single($cron_name, $cron_type, $post_id){
	
	wp_clear_scheduled_hook($cron_name, array($cron_type, $post_id));
	
}

/**
 * Post status changes to "publish".
 *
 * @since 1.2.0
 *
 */
function azrcrv_e_post_status_transition($new_status, $old_status, $post){
	
	$options = azrcrv_e_get_option('azrcrv-e');
	$to_twitter_enabled = azrcrv_e_is_plugin_active('azrcrv-to-twitter/azrcrv-to-twitter.php');
	
	if ($post->post_type == 'event' AND $to_twitter_enabled AND $options['to-twitter']['integrate'] == 1 AND $new_status == 'publish'){
		$post_id = $post->ID;
		
		$autopost_tweet = get_post_meta($post_id, '_azrcrv_e_tweet', true);
		$event_details = get_post_meta($post_id, '_azrcrv_e_event_dates', true);
	
		if ($autopost_tweet['tweet'] == 1 AND $autopost_tweet['tweeted-date'] < date("Y-m-d")){
			$cron_name = 'azrcrv_e_cron_tweet_event';
			$cron_type = 'tweet';
			
			$tweet_date = date('Y-m-d', strtotime("-".$autopost_tweet['tweet-days-before']." days", strtotime($event_details['start-date'])));
			$tweet_time = strtotime($tweet_date.' '.$autopost_tweet['tweet-time']);

			azrcrv_e_clear_cron_single($cron_name, $cron_type, $post_id);
			azrcrv_e_create_cron_single($tweet_time, $cron_name, $cron_type, $post_id);
		}
		
		if ($autopost_tweet['retweet'] == 1 AND $autopost_tweet['retweeted-date'] < date("Y-m-d")){
			$cron_name = 'azrcrv_e_cron_tweet_event';
			$cron_type = 'retweet';
			
			$retweet_date = date('Y-m-d', strtotime("-".$autopost_tweet['retweet-days-before']." days", strtotime($event_details['start-date'])));
			$retweet_time = strtotime($retweet_date.' '.$autopost_tweet['retweet-time']);
			
			azrcrv_e_clear_cron_single($cron_name, $cron_type, $post_id);
			azrcrv_e_create_cron_single($retweet_time, $cron_name, $cron_type, $post_id);
		}
    }
	
}

/**
 * Autopost tweet for post when status changes to "publish".
 *
 * @since 1.0.0
 *
 */
function azrcrv_e_check_tweet($post_id, $post){
    remove_action('wp_insert_post', 'updated_to_publish', 10, 2);
	
	$options = azrcrv_e_get_option('azrcrv-e');
	$to_twitter_enabled = azrcrv_e_is_plugin_active('azrcrv-to-twitter/azrcrv-to-twitter.php');
	
	if ($post->post_type == 'event' AND $to_twitter_enabled AND $options['to-twitter']['integrate'] == 1 AND $post->post_status == 'publish'){
		
		$autopost_tweet = get_post_meta($post_id, '_azrcrv_e_tweet', true);
		$event_details = get_post_meta($post_id, '_azrcrv_e_event_dates', true);
		
		if ($autopost_tweet['tweet'] == 1 AND $autopost_tweet['tweeted-date'] < date("Y-m-d")){
			$cron_name = 'azrcrv_e_cron_tweet_event';
			$cron_type = 'tweet';
			
			$tweet_date = date('Y-m-d', strtotime("-".$autopost_tweet['tweet-days-before']." days", strtotime($event_details['start-date'])));
			$tweet_time = strtotime($tweet_date.' '.$autopost_tweet['tweet-time']);

			azrcrv_e_clear_cron_single($cron_name, $cron_type, $post_id);
			azrcrv_e_create_cron_single($tweet_time, $cron_name, $cron_type, $post_id);
		}
		
		if ($autopost_tweet['retweet'] == 1 AND $autopost_tweet['retweeted-date'] < date("Y-m-d")){
			$cron_name = 'azrcrv_e_cron_tweet_event';
			$cron_type = 'retweet';
			
			$retweet_date = date('Y-m-d', strtotime("-".$autopost_tweet['retweet-days-before']." days", strtotime($event_details['start-date'])));
			$retweet_time = strtotime($retweet_date.' '.$autopost_tweet['retweet-time']);

			azrcrv_e_clear_cron_single($cron_name, $cron_type, $post_id);
			azrcrv_e_create_cron_single($retweet_time, $cron_name, $cron_type, $post_id);
		}
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
		
		$category = (!empty($instance['category']) ? esc_attr($instance['category']) : esc_html($options['widget']['category']));
		
		$width = (!empty($instance['width']) ? esc_attr($instance['width']) : $options['widget']['width']);
		
		$height = (!empty($instance['height']) ? esc_attr($instance['height']) : $options['widget']['height']);
		
		$limit = (!empty($instance['limit']) ? esc_attr($instance['limit']) : $options['widget']['limit']);
		
		$date_format = (!empty($instance['date-format']) ? esc_attr($instance['date-format']) : $options['widget']['date-format']);
		?>
		
		<p>
			<label for="<?php echo 
						$this->get_field_id('title'); ?>">
			<?php esc_html_e('Title:', 'events'); ?>&nbsp;			
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />			
			</label>
		</p> 
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('intro-text'); ?>">
			<?php esc_html_e('Intro Text:', 'events'); ?>&nbsp;			
			<input type="text" id="<?php echo $this->get_field_name('intro-text'); ?>" name="<?php echo $this->get_field_name('intro-text'); ?>" value="<?php echo $intro_text; ?>" />			
			</label>
		</p> 
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('category'); ?>">
			<?php esc_html_e('Category:', 'events');
			
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
			<?php esc_html_e('Width:', 'events'); ?>&nbsp;			
			<input type="number" id="<?php echo $this->get_field_name('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" class="small-text" value="<?php echo $width; ?>" />
			</label>
		</p>
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('height'); ?>">
			<?php esc_html_e('Height:', 'events'); ?>&nbsp;			
			<input type="number" id="<?php echo $this->get_field_name('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" class="small-text" value="<?php echo $height; ?>" />
			</label>
		</p>
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('limit'); ?>">
			<?php esc_html_e('Limit:', 'events'); ?>&nbsp;			
			<input type="number" id="<?php echo $this->get_field_name('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" class="small-text" value="<?php echo $limit; ?>" />
			</label>
		</p> 
		
		<p>
			<label for="<?php echo 
						$this->get_field_name('date-format'); ?>">
			<?php esc_html_e('Date Format:', 'events'); ?>&nbsp;			
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
		$output = $before_widget;
		$output .= $before_title;
		$title = (!empty($instance['title']) ? esc_attr($instance['title']) : $options['widget']['title']);
		$output .= apply_filters('widget_title', $title);
		$output .= $after_title;
		
		$intro_text = (!empty($instance['intro-text']) ? $instance['intro-text'] : '');
		$date_format = (!empty($instance['date-format']) ? esc_attr($instance['date-format']) : $options['widget']['date-format']);
		if (strlen($intro_text) > 0){
			$output .= '<p>'.wp_kses($intro_text, wp_kses_allowed_html()).'</p>';
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
				
				$output .= '<div class="azrcrv-e-container-widget">';
					$title = $event->post_title;
					
					// display widget body
					if (has_post_thumbnail($event->ID)){
						$image = wp_get_attachment_image(get_post_thumbnail_id($event->ID), array($instance['width'],$instance['height']),'', array('class' => "img-responsive alignleft", 'alt' => get_the_title()));
						
						$output .= '<div class="azrcrv-e-widget-image">'.$image.'</div>';
					}
					
					$output .= '<div class="azrcrv-e-widget-details">';
						$output .= '<p><h3 class="azrcrv-e">'.$title.'</h3></p>';
						if ($event_details['start-date'] == $event_details['end-date']){
							$end_date = '';
						}else{
							$end_date = '-'.date_format(date_create($event_details['end-date']), $date_format);
						}
						$output .= '<p class="azrcrv-e-widget-dates">'.date_format(date_create($event_details['start-date']),$date_format).$end_date.' '.$event_details['start-time'].'-'.$event_details['end-time'].'</p>';
						$output .= '<p class="azrcrv-e-widget-excerpt">'.$event->post_excerpt.'</p>';
					$output .= '</div>';
				$output .= '</div>';
				$output .= '<p class="azrcrv-e-clear" />';
			
				if ($count == $instance['limit']){ break; }
			}
		}
		if ($count == 0){
			$output .= '<p>'.sprintf(esc_html__('No %s events found.', 'events'), '<em>'.$instance['category'].'</em>').'</p>';
		}
		// display widget footer
		$output .= $after_widget;
		
		if ($count >= 1 OR ($options['widget']['hide'] != 1 AND $count == 0)){
			echo $output;
		}
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
		$output = sprintf(esc_html__('No events found for category %s', 'events'), '<em>'.$category.'</em>');
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
		$output .= '<p>'.sprintf(esc_html__('No %s events found.', 'events'), '<em>'.$slug.'</em>').'</p>';
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