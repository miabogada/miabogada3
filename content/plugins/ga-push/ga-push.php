<?php /*
Plugin Name: GA Push Filter
Plugin URI: http://f4rrest.wordpress.com/plugins/
Description: Set up the push filters for Yost Google Analtyics hooks.
Author: FC
Version: 0.1
Author URI: http://f4rrest.wordpress.com
License: GPL2
*/ 
function ga_push($push) {
	global $pagestate;
	$push[] = "'_trackPageview','/pagar/".$pagestate."/'";
	return $push;
}
//add_filter('yoast-ga-push-after-pageview', 'ga_push'); 
?>
