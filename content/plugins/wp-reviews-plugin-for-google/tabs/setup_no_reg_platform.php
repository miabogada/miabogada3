<label class="ti-left-label"><?php echo TrustindexPlugin_google::___("Google Place ID or URL"); ?>:</label>
<div class="input">
<input class="form-control"
placeholder="<?php echo TrustindexPlugin_google::___("e.g.:") . ' ' . esc_attr($example); ?>"
id="page-link"
type="text" required="required"
/>
<span class="info-text">
<?php echo TrustindexPlugin_google::___("Google Search or Google Maps URL where you can see your business profile."); ?> 
<?php echo TrustindexPlugin_google::___("For example: <a href='%s' target='_blank'>illustration</a>, <a href='%s' target='_blank'>illustration</a>", [ 'https://cdn.trustindex.io/' . 'assets/img/trustindex-google-search-1.jpg', 'https://cdn.trustindex.io/' . 'assets/img/trustindex-google-search-2.jpg' ]); ?>
<br />
<strong><?php echo TrustindexPlugin_google::___('Or enter the Place ID if you want to be sure.'); ?></strong> <a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank"><?php echo TrustindexPlugin_google::___('You can find it here after typing the address in the search bar on the map.'); ?></a>
</span>
<img class="loading" src="<?php echo admin_url('images/loading.gif'); ?>" />
<div class="results"
data-errortext="<?php echo TrustindexPlugin_google::___("Please add your Place ID again: this is not a valid %s page.", [ "Google" ]); ?>"
data-errortext-search="<?php echo TrustindexPlugin_google::___("Please give us that URL where you can see your business profile in Google Search or in Google Maps!"); ?>"
></div>
</div>
<button class="btn btn-text btn-check"><?php echo TrustindexPlugin_google::___("Check") ;?></button>