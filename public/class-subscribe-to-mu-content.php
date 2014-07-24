<?php
/**
 * Plugin Name.
 *
 * @package   Subscribe to MU Content
 * @author    Jonathan de Jong <jonathan@tigerton.se>
 * @license   GPL-2.0+
 * @link      http://tigerton.se
 * @copyright 2014 Tigerton AB
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class subscribe_to_mu_content {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'subscribe-to-mu-content';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		
		//Load includes
		add_action( 'init', array( $this, 'load_plugin_includes' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		add_shortcode( 'stmc_subscriptions', array( $this, 'display_plugin_view_shortcode' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );
		*/

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}
	
	/**
	 * Load our includes
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_includes() {
		include_once( 'includes/public-functions.php' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}
	
	
	/**
	 * Render the public view for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_view() {
		include_once( 'views/public.php' );
	}
	
	
	/**
	 * Render the public view for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_view_shortcode($attributes) {
		$return_stuff = '';
		if(!is_user_logged_in()): 
			
			$return_stuff = '
			<section class="stmc-content" id="stmc-login-content">
				<h2>'. __('You need to be logged in to manage your subscription', $this->plugin_slug) .'</h2>
				<form action="'. esc_url(site_url('wp-login.php', 'login_post')) .'" method="post" id="stmc-loginform">
				<input type="text" name="log" id="log" value="'. wp_specialchars(stripslashes($user_login), 1) .'" size="20" />
				<input type="password" name="pwd" id="pwd" size="20" />
				<input type="submit" name="submit" value="'. __('Login', $this->plugin_slug) .'" class="button" />
				    <p>
				       <label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> Remember me</label>
				       <input type="hidden" name="redirect_to" value="'. $_SERVER['REQUEST_URI'] .'" />
				    </p>
				</form>
				<a href="'. esc_url(site_url('wp-login.php?action=lostpassword', 'login_post')) .'">'. __('Recover password', $this->plugin_slug). '</a>
			</section>';
			
		else:
		
			$return_stuff = '<section class="stmc-content" id="stmc-subscription-content">';
			//Update the subscriptions!
			if (!empty($_POST['subscription_sites'])){
				check_admin_referer('save_user_subscription', 'save_subscription');
				$current_user = wp_get_current_user();
				update_user_meta($current_user->ID, 'user_subscription_sites', $_POST['subscription_sites']);
				$message = __('Your subscription has been successfully updated', $this->plugin_slug);
				$return_stuff .= '<div class="stmc-message"><p>'. $message .'</p></div>';
			}
			
			$sites = wp_get_sites();
			$general_network_settings = get_site_option('subscribe-to-mu-content_general_network_settings' );
			$current_user = wp_get_current_user();
			$current_sites = get_user_meta($current_user->ID, 'user_subscription_sites', true);
			$return_stuff .= '
			<div class="cd-popup" role="alert">
			    <div class="cd-popup-container">
			        <p>'. __('Are you sure you want to uncheck all your subscriptions?', $this->plugin_slug) .'</p>
			        <ul class="cd-buttons">
			            <li><a href="#1" class="yes">'. __('Yes', $this->plugin_slug) .'</a></li>
			            <li><a href="#0" class="no">'. __('No', $this->plugin_slug) .'</a></li>
			        </ul>
			        <a href="#0" class="cd-popup-close img-replace">Close</a>
			    </div> <!-- cd-popup-container -->
			</div>
			<div id="stmc-user-info">
				<h4>'. __('Logged in as', $this->plugin_slug) .' '. $current_user->display_name .'</h4>
				<a href="'. wp_logout_url(get_permalink()) .'">'. __('Logout', $this->plugin_slug) .'</a>
			</div>
			<form action="'.  get_permalink(get_the_ID()) .'" method="post" id="stmc-subscriptions">
				'. wp_nonce_field('save_user_subscription', 'save_subscription') .'
				<ul class="clearfix stmc-list ';
				if(count($sites) > 11){
					$return_stuff .= 'large';
				}  
				$return_stuff .= '">';
				
				$none_checked = (in_array('none', $current_sites) ? 'checked' : '');
				$return_stuff .='
					<li class="stmc-input-wrapper"><label for="none"><input type="checkbox" value="none" name="subscription_sites[]" id="none" '.  $none_checked .' />'. __('No subscription', $this->plugin_slug) .'</label></li>';
					
				foreach($sites as $site){
					if(!empty($general_network_settings['excluded_sites'])){
						if(in_array($site['blog_id'], $general_network_settings['excluded_sites'])) continue; 
					}
					$checked = (in_array($site['blog_id'], $current_sites) ? 'checked' : '');  
					$site_info = get_blog_details($site['blog_id']);
					
					$return_stuff .= '<li class="stmc-input-wrapper"><label for="site-'.  $site_info->blog_id .'"><input type="checkbox" value="'.  $site_info->blog_id .'" name="subscription_sites[]" id="site-'.  $site_info->blog_id .'" '.  $checked .' />'.  $site_info->blogname .'</label></li>';
				} 
				$return_stuff .= '</ul>';
				$return_stuff .= '<input type="submit" name="submit" class="button" id="stmc-button" value="'. __('Update subscription', $this->plugin_slug) .'" />
			</form>';
			$return_stuff .= '</section>';
			
		endif; 
		return $return_stuff;
	}

}