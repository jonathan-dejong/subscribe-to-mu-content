<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */
?>

<?php if(!is_user_logged_in()): //Visitor is not logged in ?>

	<section class="stmc-content" id="stmc-login-content">
		<h2><?php _e('You need to be logged in to manage your subscription', $this->plugin_slug); ?></h2>
		<form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post" id="stmc-loginform">
		<input type="text" name="log" id="log" value="<?php echo wp_specialchars(stripslashes($user_login), 1) ?>" size="20" />
		<input type="password" name="pwd" id="pwd" size="20" />
		<input type="submit" name="submit" value="Send" class="button" />
		    <p>
		       <label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> Remember me</label>
		       <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
		    </p>
		</form>
		<a href="<?php echo esc_url(site_url('wp-login.php?action=lostpassword', 'login_post')); ?>"><?php _e('Recover password', $this->plugin_slug); ?></a>
	</section>
	
<?php else: ?>

	<section class="stmc-content" id="stmc-subscription-content">
		<?php 	
		//Update the subscriptions!
		if (!empty($_POST['subscription_sites'])){
			check_admin_referer('save_user_subscription', 'save_subscription');
			$current_user = wp_get_current_user();
			update_user_meta($current_user->ID, 'user_subscription_sites', $_POST['subscription_sites']);
			$message = __('Your subscription has been successfully updated', $this->plugin_slug);
			echo '<div class="stmc-message"><p>'. $message .'</p></div>';
		}
		?>
		<?php
		$sites = wp_get_sites();
		$general_network_settings = get_site_option('subscribe-to-mu-content_general_network_settings' );
		$current_user = wp_get_current_user();
		$current_sites = get_user_meta($current_user->ID, 'user_subscription_sites', true);
		if($sites){
		?>
			<div class="cd-popup" role="alert">
			    <div class="cd-popup-container">
			        <p><?php _e('Are you sure you want to uncheck all your subscriptions?', $this->plugin_slug); ?></p>
			        <ul class="cd-buttons">
			            <li><a href="#1" class="yes"><?php _e('Yes', $this->plugin_slug); ?></a></li>
			            <li><a href="#0" class="no"><?php _e('No', $this->plugin_slug); ?></a></li>
			        </ul>
			        <a href="#0" class="cd-popup-close img-replace">Close</a>
			    </div> <!-- cd-popup-container -->
			</div> <!-- cd-popup -->
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" id="stmc-subscriptions">
				<?php wp_nonce_field('save_user_subscription', 'save_subscription'); ?>
				<ul class="clearfix stmc-list <?php if(count($sites) > 11) echo 'large'; ?>">
					<?php $none_checked = (in_array('none', $current_sites) ? 'checked' : '');  ?>
					<li class="stmc-input-wrapper"><label for="none"><input type="checkbox" value="none" name="subscription_sites[]" id="none" <?php echo $none_checked; ?> /><?php _e('No subscription', $this->plugin_slug); ?></label></li>
					<?php foreach($sites as $site){ ?>
						<?php if(in_array($site['blog_id'], $general_network_settings['excluded_sites'])) continue; ?>
						<?php $checked = (in_array($site['blog_id'], $current_sites) ? 'checked' : '');  ?>
						<?php $site_info = get_blog_details($site['blog_id']); ?>
						<li class="stmc-input-wrapper"><label for="site-<?php echo $site_info->blog_id; ?>"><input type="checkbox" value="<?php echo $site_info->blog_id; ?>" name="subscription_sites[]" id="site-<?php echo $site_info->blog_id; ?>" <?php echo $checked; ?> /><?php echo $site_info->blogname; ?></label></li>
					<?php } ?>
				</ul>
				<input type="submit" name="submit" class="button" id="stmc-button" value="<?php _e('Update subscription', $this->plugin_slug); ?>" />
			</form>
		<?php } ?>
	</section>
	
<?php endif; ?>