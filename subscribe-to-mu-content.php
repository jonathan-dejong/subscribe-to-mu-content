<?php
/**
 * Subscribe to MU content - by Tigerton
 *
 * A Multisite plugin which adds the ability to let signed in users subscribe to content from sites in the network by email. This plugin will then send out  
 * a new email when new content is made available on the sites the user has selected. 
 *
 * @package   Subscribe to MU Content
 * @author    Jonathan de Jong <jonathan@tigerton.se>
 * @license   GPL-2.0+
 * @link      http://tigerton.se
 * @copyright 2014 Tigerton AB
 *
 * @wordpress-plugin
 * Plugin Name:       Subscribe to MU Content
 * Plugin URI:        https://github.com/jonathan-dejong/subscribe-to-mu-content
 * Description:       Adds the ability to let signed in users subscribe to content from sites in the network by email. This plugin will then send out a new email when new content is made available on the sites the user has selected. Only Multisite compatible *duh*
 * Version:           1.0.0
 * Author:            Jonathan de Jong
 * Author URI:        www.tigerton.se
 * Text Domain:       subscribe-to-mu-content
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/jonathan-dejong/subscribe-to-mu-content
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'public/class-subscribe-to-mu-content.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'subscribe_to_mu_content', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'subscribe_to_mu_content', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'subscribe_to_mu_content', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-subscribe-to-mu-content-admin.php' );
	add_action( 'plugins_loaded', array( 'subscribe_to_mu_content_Admin', 'get_instance' ) );

}
