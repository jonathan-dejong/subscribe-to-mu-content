<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Subscribe to MU Content
 * @author    Jonathan de Jong <jonathan@tigerton.se>
 * @license   GPL-2.0+
 * @link      http://tigerton.se
 * @copyright 2014 Tigerton AB
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<?php
	//Save values
	if(isset($_POST['action']) && $_POST['action'] == 'update_'. $this->plugin_slug.'_general_settings') {
	    check_admin_referer('save_network_settings', $this->plugin_slug);
	    //store option values in a variable
	    if(isset($_POST['excluded_sites'])) $network_settings['excluded_sites'] = $_POST['excluded_sites'];

        //save option values
        $status = update_site_option( $this->plugin_slug.'_general_network_settings', $network_settings );
		write_log($status);
		if($status){
	        echo '<div id="message" class="updated fade"><p><strong>General Settings Updated</strong></p></div>';
        }else{
	        echo '<div id="message" class="error fade"><p><strong>No changes made</strong></p></div>';  
        }
	
	}elseif(isset($_POST['action']) && $_POST['action'] == 'update_'. $this->plugin_slug.'_email_settings'){
		check_admin_referer('save_network_settings', $this->plugin_slug);
	    //store option values in a variable
	    if(isset($_POST['email_from'])){
			$network_settings['from'] = $_POST['email_from'];    
	    }
	    if(isset($_POST['email_subject'])){
			$network_settings['subject'] = $_POST['email_subject'];    
	    }
	    if(isset($_POST['email_content'])){
			$network_settings['content'] = $_POST['email_content'];    
	    }
        
        //use array map function to sanitize option values
        //$network_settings = array_map( 'sanitize_text_field', $network_settings );

        //save option values
        $status = update_site_option( $this->plugin_slug.'_email_network_settings', $network_settings );
		if($status){
	        echo '<div id="message" class="updated fade"><p><strong>Email Settings Updated</strong></p></div>';
        }else{
	        echo '<div id="message" class="error fade"><p><strong>No changes made</strong></p></div>';  
        }
	}
	
	//Tabs
	if ( isset ( $_GET['tab'] ) ) $this->admin_tabs($_GET['tab']); else $this->admin_tabs('general');
	if (isset($_GET['tab'])){ $tab = $_GET['tab']; }else{ $tab = 'general'; } 
	?>
	<?php if($tab == 'general'){ //General settings ?>
		<?php $general_network_settings = get_site_option( $this->plugin_slug.'_general_network_settings' );  ?>
		<form method="post">
			<input type="hidden" name="action" value="update_<?php echo $this->plugin_slug; ?>_general_settings" />
			<?php wp_nonce_field('save_network_settings', $this->plugin_slug); ?>
			<h3><?php _e('General settings', $this->plugin_slug); ?></h3>
			<p><?php _e('These are some general settings', $this->plugin_slug); ?></p>
			<table class="form-table">
                <tr valign="top"><th scope="row"><?php _e('Exclude sites:', $this->plugin_slug); ?></th>
                    <td>
                        <?php 
						$excluded_sites = $general_network_settings['excluded_sites'];
						$sites = wp_get_sites();
						if($sites){
							foreach($sites as $site){
								$site_info = get_blog_details($site['blog_id']);
								if(!empty($excluded_sites)){
									$checked = (in_array($site_info->blog_id, $excluded_sites) ? 'checked' : '');
								}else{
									$checked = '';
								}
								echo '<label><input type="checkbox" name="excluded_sites[]" id="excluded_sites" value="' . $site_info->blog_id . '" ' . $checked . '/> ' . $site_info->blogname . '</label></br>';
							}
						}
                        ?>
                    </td>
                </tr>
            </table>
			<?php submit_button(); ?>
		</form>
	
	<?php }elseif($tab == 'emails'){ //Email settings ?>
		<?php $email_network_settings = get_site_option( $this->plugin_slug.'_email_network_settings' );  ?>
		<form method="post">
			<input type="hidden" name="action" value="update_<?php echo $this->plugin_slug; ?>_email_settings" />
			<?php wp_nonce_field('save_network_settings', $this->plugin_slug); ?>
			<h3><?php _e('Email settings', $this->plugin_slug); ?></h3>
			<p><?php _e('These settings allow you to set the look and feel of the mail being sent out to subscribers. Any information you put in the editor below will be prepended to the basic information about the post that\'s been published', $this->plugin_slug); ?></p>
			<table class="form-table">
                <tr valign="top"><th scope="row"><?php _e('From:', $this->plugin_slug); ?></th>
                    <td>
                        <input type="text" name="email_from" id="email_from" value="<?php echo ($email_network_settings['from'] ? $email_network_settings['from'] : ''); ?>" size="50" />                  
                    </td>
                </tr>
                <tr valign="top"><th scope="row"><?php _e('Subject:', $this->plugin_slug); ?></th>
                    <td>
                        <input type="text" name="email_subject" id="email_subject" value="<?php echo ($email_network_settings['subject'] ? $email_network_settings['subject'] : ''); ?>" size="50" />                  
                    </td>
                </tr>
                <tr valign="top"><th scope="row"><?php _e('From:', $this->plugin_slug); ?></th>
                    <td>
                        <?php
                        $content = ($email_network_settings['content'] ? $email_network_settings['content'] : '');
                        $settings = array( //tinyMCE settings
							'textarea_name' => 'email_content',
							'wpautop' => true
						);
						wp_editor($content, 'email_content', $settings ); //create an editor
                        ?>                
                    </td>
                </tr>
            </table>
			<?php submit_button(); ?>
		</form>
	<?php } ?>	
	
</div>
