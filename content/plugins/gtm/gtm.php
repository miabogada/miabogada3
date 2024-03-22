<?php /*
Plugin Name: GTM
Plugin URI: http://f4rrest.wordpress.com/plugins/
Description: Add GTM tags.
Author: FC
Version: 0.1
Author URI: http://f4rrest.wordpress.com
License: GPL2
*/ 
function google_tag_head_script() {
?><!-- Google Tag Manager -->
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');
</script>
<!-- End Google Tag Manager -->
<?php

}
add_action( 'wp_head', 'google_tag_head_script' );

function google_tag_after_body_script() {
?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T29KMCW"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) --><?php

}
add_action('wp_footer', 'google_tag_after_body_script');
?>
