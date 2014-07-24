<?php

function show_stmc_subscriptions(){
	//fetch instance of our class
	$subscription = subscribe_to_mu_content::get_instance();
	//run function that renders view!
	$subscription->display_plugin_view();
}

?>