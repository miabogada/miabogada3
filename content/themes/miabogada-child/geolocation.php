<?php
include('../../../../includes/domaintools.php');
$url = 'http://engine.whoisapi.com/api.xml?application=ip_location&version=2&partner=' .$domaintools_partner .'&key=' .$domaintools_key .'&customer_ip=&ip=' .$_SERVER['REMOTE_ADDR'];
$xml = simpleXML_load_file($url,"SimpleXMLElement",LIBXML_NOCDATA);
if($xml ===  FALSE){
	$location = 'unknown location'; //deal with error
} else { 
	$location = $xml->response->city .', ' .$xml->response->region; 
}
//	echo "\n" .$location ."\n";
?>
