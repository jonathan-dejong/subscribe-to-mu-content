<?php
/**
 * Plugin Name.
 *
 * @package   subscribe-to-mu-content_Admin
 * @author    Jonathan de Jong <jonathan@tigerton.se>
 * @license   GPL-2.0+
 * @link      http://tigerton.se
 * @copyright 2014 Tigerton AB
 */

/**
 *
 * @package subscribe_to_mu_content_Admin
 * @author  Jonathan de Jong jonathan@tigerton.se 
 */
class subscribe_to_mu_content_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		if( ! is_super_admin() ) {
			return;
		}

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = subscribe_to_mu_content::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the menu page to network admin.
		add_action( 'network_admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		
		/*
		add_action( 'admin_init', array( $this, 'subscribe_to_mu_content_register_settings' ) );
		*/
		
		// Run whenever a post is saved or updated!
		add_action( 'save_post', array( $this, 'setup_email' ), 99, 2 );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		 /*
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );
		*/

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		if( ! is_super_admin() ) {
			return;
		}

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), subscribe_to_mu_content::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), subscribe_to_mu_content::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Subscribe to MU Content settings', $this->plugin_slug ), 
			__( 'MU Subscriptions', $this->plugin_slug ), 
			'manage_network', 
			$this->plugin_slug, 
			array( $this, 'display_plugin_admin_page' )
		);

	}
	
	
	/*
	*  Create tabs
	*
	*  @description: 
	*  @since 1
	*  @created: 2013-07-21
	*/
	public function admin_tabs( $current = 'general' ) { 
	    $tabs = array( 'general' => __('General', $this->plugin_slug), 'emails' => __('Emails', $this->plugin_slug)); 
	    $links = array();
	    echo '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        echo "<a class='nav-tab $class' href='?page=" . $this->plugin_slug . "&tab=$tab'>$name</a>";
	        
	    }
	    echo '</h2>';
	}
	

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}
	
	
	/**
	 * 
	 * Sends out the emails to all the subscribers whenever a post has been updated
	 * Uses save_post hook
	 * @since    1.0.0
	 */
	public function setup_email($post_id, $post){

		// If this is just a revision, don't send the email.
		if(wp_is_post_revision($post_id))
			return;
		// If this already has a post meta it's not a new post
		$updated = get_post_meta($post_id, 'updated', true);
		if($updated)
			return;
		
		//set the control meta
		update_post_meta($post_id, 'updated', true);
		//Fetch subscribers
		$subscribers = $this->fetch_subscribers_emails();
		//If we have subscribers, get crackin!
		if(!empty($subscribers->results)){
			foreach($subscribers->results as $subscriber){
				$subscriber_emails[] = 	$subscriber->user_email;
			}
			//Setup the email!
			$post_title = $post->post_title;
			$post_url = get_permalink($post_id);
			$email_network_settings = get_site_option($this->plugin_slug.'_email_network_settings');
			$subject = ($email_network_settings['subject'] != '' ? $email_network_settings['subject'] : __('A new post has been published', $this->plugin_slug));
			$from = ($email_network_settings['from'] != '' ? $email_network_settings['from'] : get_option('admin_email'));		
			$message = ($email_network_settings['content'] != '' ? apply_filters('the_content', $email_network_settings['content']) : '');
			$message .= $post_title . ": " . $post_url;
			$mail_status = $this->send_emails($subscriber_emails, $subject, $message, $from);
		}
	}
	
	
	/**
	 * 
	 * Fetch all users who wants the email yo!
	 * @since    1.0.0
	 */
	public function fetch_subscribers_emails(){
	
		$blog_id = get_current_blog_id();
		$args = array(
			'blog_id' => 1,
			'meta_query' => array(
				array(
					'key' => 'user_subscription_sites',
					'value' => $blog_id,
					'compare' => 'LIKE'
				)
			),
			'fields' => array('user_email')
		);
		$subscribers = new WP_User_Query($args);
		return $subscribers;
	}

	/**
	 * 
	 * Sends out emails!
	 * $to = String or array of email adresses, required.
	 * @since    1.0.0
	 */
	public function send_emails($to, $subject = '', $message = '', $from = '', $attachments = ''){
		add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
		return wp_mail($to, $subject, $message, $from, $attachments);
	}
	
	/**
	 * 
	 * Set email content to allow HTML
	 * @since    1.0.0
	 */
	function set_html_content_type() {
		return 'text/html';
	}

	

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
