<?php /*
Plugin Name: Sitemap XML
Plugin URI: http://f4rrest.wordpress.com/plugins/
Description: create a simple sitemap at /sitemap.xml
Author: FC
Version: 0.1
Author URI: http://f4rrest.wordpress.com
License: GPL2
*/
add_action( 'publish_post', 'itsg_create_sitemap' );
add_action( 'publish_page', 'itsg_create_sitemap' );

function itsg_create_sitemap() {

    // get date of latest post
    $args = array(
      'post_type' => array('post','page'),
      'posts_per_page' => 1,
    );
    $last_updated = new WP_Query($args);

    // get all the posts
    $postsForSitemap = get_posts(array(
        'numberposts' => -1,
        'orderby' => 'modified',
        'post_type'  => array( 'post', 'page' ),
        'order'    => 'DESC'
    ));

    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach( $postsForSitemap as $post ) {
        setup_postdata( $post );

	if ($post->ID == "26" or $post->ID == "237") {
		$postdate = explode( " ", $last_updated->post->post_date );
	} else {
	      	$postdate = explode( " ", $post->post_modified );
	}

	$dStart = new DateTime($postdate[0]); 
	$dEnd  = new DateTime();
	$dDiff = $dStart->diff($dEnd);
	$days = (int)$dDiff->format("%r%a");

	if ($days < "7" or $post->ID == "2" or $post->ID == "26" or $post->ID == "237") {
    		$freq = "daily";
	} elseif ($days < "30") {
    		$freq = "weekly";
	} elseif ($days < "60") {
    		$freq = "monthly";
	} else {
		$freq = "yearly";
	}


        $sitemap .= '<url>'.
          '<loc>' . str_replace("http://www0.","https://www.",get_permalink( $post->ID )) . '</loc>' .
          '<lastmod>' . $postdate[0] . '</lastmod>' .
          '<changefreq>' . $freq . '</changefreq>' .
         '</url>';

      }

    $sitemap .= '</urlset>';

    $fp = fopen( WP_CONTENT_DIR . '/sitemap.xml', 'w' );

    fwrite( $fp, $sitemap );
    fclose( $fp );
}
