<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$ti_command = isset($_REQUEST['command']) ? sanitize_text_field($_REQUEST['command']) : null;
$ti_command_list = [
'save-page',
'delete-page',
'save-style',
'save-filter',
'save-set',
'save-language',
'save-dateformat',
'save-options',
'save-align',
'save-amp-notice-hide'
];
if(!in_array($ti_command, $ti_command_list))
{
$ti_command = null;
}
function trustindex_database_create_check($table_exists_check = false)
{
global $wpdb;
global $trustindex_pm_google;
$last_error = $wpdb->last_error;
if($trustindex_pm_google->is_noreg_table_exists() !== $table_exists_check)
{
return;
}
echo '
<div class="ti-notice notice-error" style="margin: 25px 0 0 0">
<p>
'. TrustindexPlugin_google::___('We can not create MySQL table for the reviews!') .'<br /><br />
'. TrustindexPlugin_google::___('We got the following error from %s:', [ 'MySQL' ]) .'<br >
<strong>'. $last_error .'</strong>
</p>
</div>';
exit;
}
function trustindex_plugin_connect_page($page_details = null, $default_settings = true)
{
global $trustindex_pm_google;
global $wpdb;
if(!$page_details)
{
return false;
}
$dbtable = $trustindex_pm_google->get_noreg_tablename();
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
$wpdb->hide_errors();
if($trustindex_pm_google->is_noreg_table_exists())
{
$wpdb->query('DROP TABLE `'. $dbtable .'`');
trustindex_database_create_check(true);
}
dbDelta("CREATE TABLE $dbtable (
id TINYINT(1) NOT NULL AUTO_INCREMENT,
user VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci,
user_photo TEXT,
text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
rating DECIMAL(3,1),
highlight VARCHAR(11),
date DATE,
PRIMARY KEY (id)
);");
trustindex_database_create_check();
if (!isset($page_details['rating_number']) || !isset($page_details['avatar_url']) || empty($page_details['avatar_url']))
{
$response = $trustindex_pm_google->download_noreg_details($page_details);
if ($response['success'])
{
if(!isset($page_details['rating_number'])) $page_details['rating_number'] = $response['result']['reviews']['count'];
if(!isset($page_details['rating_score'])) $page_details['rating_score'] = $response['result']['reviews']['score'];
if(!isset($page_details['avatar_url']) || empty($page_details['avatar_url'])) $page_details['avatar_url'] = $response['result']['avatar_url'];
}
}
$reviews = null;
if(isset($page_details['reviews']))
{
$reviews = $page_details['reviews'];
unset($page_details['reviews']);
}
update_option( $trustindex_pm_google->get_option_name('page-details') , $page_details, false );
$GLOBALS['wp_object_cache']->delete( $trustindex_pm_google->get_option_name('page-details'), 'options' );
if(!$reviews)
{
$reviews = $trustindex_pm_google->download_noreg_reviews($page_details, null);
$reviews = $reviews['success'] ? $reviews['result'] : [];
}
foreach($reviews as $row)
{
$date = isset($row['created_at']) ? $row['created_at'] : (isset($row['date']) ? $row['date'] : '');
$wpdb->insert($dbtable, [
'user' => $row['reviewer']['name'],
'user_photo' => $row['reviewer']['avatar_url'],
'text' => $row['text'],
'rating' => $row['rating'] ? $row['rating'] : 5,
'date' => substr($date, 0, 10)
]);
}
if($default_settings)
{
$lang = strtolower(substr(get_locale(), 0, 2));
if(!isset(TrustindexPlugin_google::$widget_languages[$lang]))
{
$lang = 'en';
}
update_option( $trustindex_pm_google->get_option_name('lang') , $lang, false );
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
}
else
{
$trustindex_pm_google->noreg_save_css(true);
}
}
function trustindex_plugin_disconnect_page($settings_delete = true)
{
global $trustindex_pm_google;
global $wpdb;
delete_option( $trustindex_pm_google->get_option_name('page-details') );
delete_option( $trustindex_pm_google->get_option_name('review-content') );
delete_option( $trustindex_pm_google->get_option_name('css-content') );
if(is_file($trustindex_pm_google->getCssFile()))
{
unlink($trustindex_pm_google->getCssFile());
}
if($settings_delete)
{
delete_option( $trustindex_pm_google->get_option_name('style-id') );
delete_option( $trustindex_pm_google->get_option_name('scss-set') );
delete_option( $trustindex_pm_google->get_option_name('filter') );
delete_option( $trustindex_pm_google->get_option_name('lang') );
delete_option( $trustindex_pm_google->get_option_name('dateformat') );
delete_option( $trustindex_pm_google->get_option_name('no-rating-text') );
delete_option( $trustindex_pm_google->get_option_name('verified-icon') );
delete_option( $trustindex_pm_google->get_option_name('enable-animation') );
delete_option( $trustindex_pm_google->get_option_name('show-arrows') );
delete_option( $trustindex_pm_google->get_option_name('show-reviewers-photo') );
delete_option( $trustindex_pm_google->get_option_name('widget-setted-up') );
}
$dbtable = $trustindex_pm_google->get_noreg_tablename();
$wpdb->query("TRUNCATE `$dbtable`;");
}
function trustindex_plugin_change_step($step = 5)
{
global $trustindex_pm_google;
if($step < 5)
{
$options_to_delete = [
'widget-setted-up',
'align',
'verified-icon',
'enable-animation',
'no-rating-text',
'disable-font',
'show-reviewers-photo',
'show-logos',
'show-stars'
];
foreach($options_to_delete as $name)
{
delete_option($trustindex_pm_google->get_option_name($name));
}
}
if($step < 4)
{
delete_option($trustindex_pm_google->get_option_name('scss-set'));
}
if($step < 3)
{
delete_option($trustindex_pm_google->get_option_name('style-id'));
}
if($step < 2)
{
trustindex_plugin_disconnect_page();
}
}
if($ti_command == 'save-page')
{
check_admin_referer( 'save-noreg_'.$trustindex_pm_google->get_plugin_slug(), '_wpnonce_save' );
$page_details = isset($_POST['page_details']) ? wp_kses_post($_POST['page_details']) : null;
$page_details = json_decode(stripcslashes($page_details), true);

trustindex_plugin_connect_page($page_details);
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
exit;
}
elseif($ti_command == 'delete-page')
{
trustindex_plugin_disconnect_page();
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
exit;
}
elseif($ti_command == 'save-style')
{
$style_id = sanitize_text_field($_REQUEST['style_id']);
update_option( $trustindex_pm_google->get_option_name('style-id') , $style_id, false );
delete_option( $trustindex_pm_google->get_option_name('review-content') );
trustindex_plugin_change_step(3);
if(in_array($style_id, [ 17, 21 ]))
{
$trustindex_pm_google->noreg_save_css();
}
if(isset($_GET['style_id']))
{
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
}
exit;
}
elseif($ti_command == 'save-set')
{
update_option( $trustindex_pm_google->get_option_name('scss-set') , sanitize_text_field($_REQUEST['set_id']), false );
trustindex_plugin_change_step(4);
$trustindex_pm_google->noreg_save_css(true);
if(isset($_GET['set_id']))
{
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
}
exit;
}
elseif($ti_command == 'save-filter')
{
$filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : null;
$filter = json_decode(stripcslashes($filter), true);
update_option( $trustindex_pm_google->get_option_name('filter') , $filter, false );
exit;
}
elseif($ti_command == 'save-language')
{
check_admin_referer( 'save-language_'.$trustindex_pm_google->get_plugin_slug(), '_wpnonce_language' );
update_option( $trustindex_pm_google->get_option_name('lang') , sanitize_text_field($_POST['lang']), false );
delete_option( $trustindex_pm_google->get_option_name('review-content') );
exit;
}
elseif($ti_command == 'save-dateformat')
{
check_admin_referer( 'save-dateformat_'.$trustindex_pm_google->get_plugin_slug(), '_wpnonce_dateformat' );
update_option( $trustindex_pm_google->get_option_name('dateformat') , sanitize_text_field($_POST['dateformat']), false );
exit;
}
elseif($ti_command == 'save-options')
{
check_admin_referer( 'save-options_'.$trustindex_pm_google->get_plugin_slug(), '_wpnonce_options' );
$r = 0;
if(isset($_POST['verified-icon']))
{
$r = sanitize_text_field($_POST['verified-icon']);
}
update_option( $trustindex_pm_google->get_option_name('verified-icon') , $r, false );
$r = 1;
if(isset($_POST['enable-animation']))
{
$r = sanitize_text_field($_POST['enable-animation']);
}
update_option( $trustindex_pm_google->get_option_name('enable-animation') , $r, false );
$r = 1;
if(isset($_POST['show-arrows']))
{
$r = sanitize_text_field($_POST['show-arrows']);
}
update_option( $trustindex_pm_google->get_option_name('show-arrows') , $r, false );
$r = 1;
if(isset($_POST['show-reviewers-photo']))
{
$r = sanitize_text_field($_POST['show-reviewers-photo']);
}
update_option( $trustindex_pm_google->get_option_name('show-reviewers-photo') , $r, false );
$r = 0;
if(isset($_POST['no-rating-text']))
{
$r = sanitize_text_field($_POST['no-rating-text']);
}
update_option( $trustindex_pm_google->get_option_name('no-rating-text') , $r, false );
$r = 0;
if(isset($_POST['disable-font']))
{
$r = sanitize_text_field($_POST['disable-font']);
}
update_option( $trustindex_pm_google->get_option_name('disable-font') , $r, false );
$r = 1;
if(isset($_POST['show-logos']))
{
$r = sanitize_text_field($_POST['show-logos']);
}
update_option( $trustindex_pm_google->get_option_name('show-logos') , $r, false );
$r = 1;
if(isset($_POST['show-stars']))
{
$r = sanitize_text_field($_POST['show-stars']);
}
update_option( $trustindex_pm_google->get_option_name('show-stars') , $r, false );
delete_option( $trustindex_pm_google->get_option_name('review-content') );
$trustindex_pm_google->noreg_save_css(true);
exit;
}
elseif($ti_command == 'save-align')
{
check_admin_referer( 'save-align_'.$trustindex_pm_google->get_plugin_slug(), '_wpnonce_align' );
update_option( $trustindex_pm_google->get_option_name('align') , sanitize_text_field($_POST['align']), false );
$trustindex_pm_google->noreg_save_css(true);
exit;
}
elseif($ti_command == 'save-amp-notice-hide')
{
update_option( $trustindex_pm_google->get_option_name('amp-hidden-notification'), 1, false );
exit;
}
$reviews = [];
$only_ratings_default = false;
if($trustindex_pm_google->is_noreg_linked())
{
$reviews = $wpdb->get_results('SELECT * FROM '. $trustindex_pm_google->get_noreg_tablename() .' ORDER BY date DESC');
$reviews_with_text = 0;
foreach($reviews as $r)
{
if($r->text)
{
$reviews_with_text++;
}
}
$only_ratings_default = $reviews_with_text >= 3;
}
$style_id = get_option( $trustindex_pm_google->get_option_name('style-id') );
$scss_set = get_option( $trustindex_pm_google->get_option_name('scss-set') );
$lang = get_option( $trustindex_pm_google->get_option_name('lang'), 'en');
$dateformat = get_option( $trustindex_pm_google->get_option_name('dateformat'), 'Y-m-d' );
$no_rating_text = get_option( $trustindex_pm_google->get_option_name('no-rating-text'), $trustindex_pm_google->get_default_no_rating_text($style_id, $scss_set) );
$filter = get_option( $trustindex_pm_google->get_option_name('filter'), [ 'stars' => [1, 2, 3, 4, 5], 'only-ratings' => $only_ratings_default ] );
$verified_icon = get_option( $trustindex_pm_google->get_option_name('verified-icon'), 0 );
$enable_animation = get_option( $trustindex_pm_google->get_option_name('enable-animation'), 1 );
$show_arrows = get_option( $trustindex_pm_google->get_option_name('show-arrows'), 1 );
$widget_setted_up = get_option( $trustindex_pm_google->get_option_name('widget-setted-up'), 0);
$disable_font = get_option( $trustindex_pm_google->get_option_name('disable-font'), 0 );
$align = get_option( $trustindex_pm_google->get_option_name('align'), in_array($style_id, [ 36, 37, 38, 39 ]) ? 'center' : 'left' );
$scss_set_tmp = $scss_set ? $scss_set : 'light-background';
$show_reviewers_photo = get_option( $trustindex_pm_google->get_option_name('show-reviewers-photo'), TrustindexPlugin_google::$widget_styles[$scss_set_tmp]['reviewer-photo'] ? 1 : 0 );
$show_logos = get_option( $trustindex_pm_google->get_option_name('show-logos'), TrustindexPlugin_google::$widget_styles[$scss_set_tmp]['hide-logos'] ? 0 : 1 );
$show_stars = get_option( $trustindex_pm_google->get_option_name('show-stars'), TrustindexPlugin_google::$widget_styles[$scss_set_tmp]['hide-stars'] ? 0 : 1 );
$need_to_refresh = false;
if($trustindex_pm_google->is_noreg_linked() && $trustindex_pm_google->is_ten_scale_rating_platform())
{
$fields = $wpdb->get_results('SHOW FIELDS FROM `'. $trustindex_pm_google->get_noreg_tablename() .'` WHERE Field = "rating"');
if($fields && isset($fields[0]) && isset($fields[0]->Type))
{
if($fields[0]->Type == 'tinyint(1)')
{
$wpdb->query('ALTER TABLE `'. $trustindex_pm_google->get_noreg_tablename() .'` CHANGE `rating` `rating` DECIMAL(3,1) NULL DEFAULT NULL');
$need_to_refresh = true;
}
}
}
if(isset($_GET['refresh']) || $need_to_refresh)
{
$page_details = get_option( $trustindex_pm_google->get_option_name('page-details') );
$tmp = $trustindex_pm_google->download_noreg_details($page_details);
if($tmp['success'])
{
$page_details['rating_number'] = $tmp['result']['reviews']['count'];
$page_details['rating_score'] = $tmp['result']['reviews']['score'];
$page_details['avatar_url'] = $tmp['result']['avatar_url'];
}
trustindex_plugin_disconnect_page(false);
trustindex_plugin_connect_page($page_details, false);
if(isset($_GET['my_reviews']))
{
setcookie('ti-success', 'reviews-loaded', time() + 60, "/");
}
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) . (isset($_GET['my_reviews']) ? '&tab=my_reviews' : '&tab=setup_no_reg'));
exit;
}
if(isset($_GET['recreate']))
{
$trustindex_pm_google->uninstall();
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
exit;
}
if(isset($_GET['setup_widget']))
{
update_option( $trustindex_pm_google->get_option_name('widget-setted-up') , 1, false );
header('Location: admin.php?page=' . sanitize_text_field($_GET['page']) .'&tab=setup_no_reg');
exit;
}
$current_step = isset($_GET['step']) ? intval(sanitize_text_field($_GET['step'])) : 0;
if($current_step == 3 && in_array($style_id, [ 17, 21 ]))
{
$current_step = 4;
}
if(!$trustindex_pm_google->is_noreg_linked())
{
$style_id = null;
$scss_set = null;
$widget_setted_up = null;
}
wp_enqueue_style("trustindex-widget-preview-css", "https://cdn.trustindex.io/assets/ti-preview-box.css");
$example = 'HairPalace';
$example_url = null;
switch("google")
{
case 'airbnb':
$example_url = 'https://www.airbnb.com/rooms/2861469';
break;
case 'amazon':
$example_url = 'https://www.amazon.com/sp?seller=A2VE8XCDXE9M4H';
break;
case 'arukereso':
$example_url = 'https://www.arukereso.hu/stores/media-markt-online-s66489';
break;
case 'booking':
$example_url = 'https://www.booking.com/hotel/us/four-seasons-san-francisco.html';
break;
case 'capterra':
$example_url = 'https://www.capterra.com/p/192416/MicroStation';
break;
case 'ebay':
$example_url = 'https://www.ebay.com/fdbk/feedback_profile/scarhead1';
break;
case 'foursquare':
$example_url = 'https://foursquare.com/v/lands-end-lookout/4f839a12e4b049ff96c6b29a';
break;
case 'hotels':
$example_url = 'https://www.hotels.com/ho108742';
break;
case 'opentable':
$example_url = 'https://www.opentable.com/r/historic-johns-grill-san-francisco';
break;
case 'szallashu':
$example_url = 'https://revngo.com/ramada-by-wyndham-city-center-hotel-budapest';
break;
case 'thumbtack':
$example_url = 'https://www.thumbtack.com/ca/san-francisco/handyman/steve-switchenko-installations-handyman-services/service/246750705829561442';
break;
case 'tripadvisor':
$example_url = 'https://www.tripadvisor.com/Restaurant_Review-g186338-d5122082-Reviews-Alexander_The_Great-London_England.html';
break;
case 'trustpilot':
$example_url = 'https://www.trustpilot.com/review/generalitravelinsurance.com';
break;
case 'expedia':
$example_url = 'https://www.expedia.com/London-Hotels-The-Hayden-Pub-Rooms.h39457643.Hotel-Information';
break;
case 'google':
$example = 'ChIJ9TmAVZfbQUcROoTJtH8TuFU';
break;
case 'yelp':
$example_url = 'https://www.yelp.ie/biz/the-iveagh-gardens-dublin-2';
break;
case 'zillow':
$example_url = 'https://www.zillow.com/profile/NealandNealTeam/#reviews';
break;
}
?>