<?php /*
Plugin Name: PayPal Framework IPN Processor
Plugin URI: http://f4rrest.wordpress.com/plugins/
Description: A plugin that creates a skeleton to processes the paypal IPN actions thrown by the Paypal Framework plugin IPN listener. Requires Paypal Framework plugin.
Author: FC
Version: 0.2
Author URI: http://f4rrest.wordpress.com
License: GPL2
*/ 
 
add_action('paypal-ipn', 'pp_ipn'); function pp_ipn($unused) { 
} 

add_action('paypal-web_accept', 'pp_ipn_webaccept'); function pp_ipn_webaccept($_POST) {
	$headers = "From: " . $_POST['payer_email'] ."<".$_POST['payer_email'].">" . "\r\n" . "Reply-To: " . $_POST['payer_email'];
	$subject = "PAID: " . $_POST['item_name'];
	$to_debug = get_bloginfo('admin_email');
	$touser = get_user_by('login','pregunta');
	if ($touser) {	
		wp_mail($touser->user_email, $subject, "Transaction details:\r\n".print_r($_POST, true), $headers);
	} else {
		wp_mail($to_debug, $subject, "Actions thrown: paypal-ipn{$specificAction}\r\n\r\nPassed to action:\r\n".print_r($_POST, true), $headers);
	}
}
?>
