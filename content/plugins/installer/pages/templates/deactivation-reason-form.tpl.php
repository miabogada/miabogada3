<script language="javascript">
jQuery(document).ready(function()
{
	wprc.uninstallReport.ajaxurl="<?php echo admin_url('admin.php').'?wprc_c=repository-reporter&wprc_action=sendUninstallReport'; ?>";
	<?php if ($bulk_deactivate==1) { ?>
		wprc.uninstallReport.action="bulk_action";
	<?php } else { ?>
		wprc.uninstallReport.action="single_action";
		wprc.uninstallReport.gotourl="<?php echo $url.'&reported=true'; ?>";
	<?php } ?>
    wprc.uninstallReport.init();
});
</script>

<h1><?php echo __('Indication about problems','installer'); ?></h1>

<?php if ($bulk_deactivate==1) { ?>
<!-- normal deactivation form -->
<form method="post" action="<?php echo $url; ?>" id="deactivation_form">
<input type="hidden" name="reported" value="true">
<input type="hidden" name="_wpnonce" value="<?php echo $bulk_data['_wpnonce']; ?>">
<input type="hidden" name="_wp_http_referer" value="<?php echo $bulk_data['_wp_http_referer']; ?>">
<input type="hidden" name="action" value="<?php echo $bulk_data['action']; ?>">
<input type="hidden" name="action2" value="<?php echo $bulk_data['action2']; ?>">
<?php foreach ($bulk_data['checked'] as $bulk_one) { ?>
<input type="hidden" name="checked[]" value="<?php echo $bulk_one; ?>">
<?php } ?>
</form>
<?php } ?>

<!-- our deactivation report form -->
<form method="post" action="<?php echo admin_url('admin.php').'?wprc_c=repository-reporter&wprc_action=sendUninstallReport'; ?>" id="deactivation_report_form">
<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-deactivation-form'); ?>">
<input type="hidden" name="site_id" value="<?php echo $site_id; ?>">

<?php
    for($i=0; $i<count($extension_keys); $i++)
    {
        echo '<input type="hidden" name="extension_keys[]" value="'.$extension_keys[$i].'">';
    }
?>
<br />
<input type="hidden" name="extension_type" value="<?php echo $extension_type; ?>">
<!--input type="hidden" name="bulk_deactivate" value="<?php echo $bulk_deactivate; ?>">-->
<table cellpadding="3px">
<tr>
<td>
    <div id="urc1-wrap" class="wprc option-wrap">
        <label>
            <input type="radio" id="urc1" name="uninstall_reason_code" value="1" onchange="wprc.uninstallReport.showDetails('#urc1')"> 
            <span><?php echo $language['i_dont_need_what_this_extension_do']; ?></span>
        </label>
    </div>
</td>
</tr>

<tr>
<td>
    <div id="urc2-wrap" class="wprc option-wrap">
        <label>
            <input type="radio" id="urc2" name="uninstall_reason_code" value="2" onchange="wprc.uninstallReport.showDetails('#urc2')"> 
            <span><?php echo $language['i_dont_like_how_the_extension_performs_its_tasks']; ?></span>
        </label>
    </div>
</td>
</tr>

<tr>
<td>
   <div id="urc3-wrap" class="wprc option-wrap">
   <label>
        <input type="radio" id="urc3" name="uninstall_reason_code" value="3" onchange="wprc.uninstallReport.showDetails('#urc3')"> 
        <span><?php echo $language['extension_is_not_working_right_on_my_site']; ?></span>
   </label> 
    <div class="wprc validation-errors" id="urc3-errors"></div>
    <div id="urc3-box" class="wprc details-boxes">
        <table>
        <?php 
        if($show_themes_list) 
        {
        ?>
        <tr>
            <td>
                <label>
                    <input id="urc31" name="uninstall_reason_code_child" value="1" type="radio" onchange="wprc.uninstallReport.showSubdetails('#urc31')"> 
                    <span><?php echo __('There is a problem with themes','installer'); ?></span>
                </label>
                <div class="wprc subdetails-boxes" id="urc31-subbox">
                    <select id="theme-select" size="5" name="problem_themes[]" multiple="multiple" style="display: none;">
                	<?php 
                    foreach($themes AS $key => $theme)
                    {
                        echo '<option value="'.$key.'"> '.$theme['Name'].'</option>';
                    }
                    ?>
                	</select>
                </div>
                <div class="wprc validation-errors" id="urc31-errors"></div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td>
                <label>
                    <input id="urc32" name="uninstall_reason_code_child" value="2" type="radio" onchange="wprc.uninstallReport.showSubdetails('#urc32')">
                    <span><?php echo __('There is a problem with plugins','installer'); ?></span>
                </label>
                <div class="wprc subdetails-boxes" id="urc32-subbox" >
                    <select id="plugin-select" size="5" name="problem_plugins[]" multiple="multiple" style="display: none;">
              		<?php 
                    foreach($plugins AS $key => $plugin)
                    {
                        echo '<option value="'.$key.'"> '.$plugin['Name'].'</option>';
                    }
                    ?>
                	</select>
                </div>
                <div class="wprc validation-errors" id="urc32-errors"></div>
            </td>
        </tr>
        
        <tr>
            <td>
                <label>
                    <input id="urc30" name="uninstall_reason_code_child" value="0" type="radio" onchange="wprc.uninstallReport.showSubdetails('#urc30')">
                    <span><?php echo __('I\'m not sure what\'s causing the problem','installer'); ?></span>
                </label>
            </td>
        </tr>
        </table>
    </div>
    </div>
</td>
</tr>

<tr>
<td>
   <div id="urc0-wrap" class="wprc option-wrap">
   <label>
        <input type="radio" id="urc0" name="uninstall_reason_code" value="0" onchange="wprc.uninstallReport.showDetails('#urc0')">
        <span><?php echo __('Something else','installer'); ?></span>
    </label>
    </div>
</td>
</tr>

<tr>
<td>
<p><strong><?php echo $language['describe_uninstalling_reason']; ?></strong></p>
    <textarea class="wprc box" name="uninstall_reason_description"></textarea>
</td>
</tr>

<tr>
<td>
	<div id="wprc-loader"></div>
    <input type="button" onclick="wprc.uninstallReport.submitDeactivationForm('deactivation_report_form','deactivation_form')" value="<?php echo $language['report_and_uninstall']; ?>">
    <input type="button" onclick="wprc.uninstallReport.skipUninstallReport('deactivation_report_form','deactivation_form')" value="<?php echo __('Skip','installer'); ?>">
</td>
</tr>


<tr>
<td><div class="wprc validation-errors" id="below-submit-errors"></div></td>
</tr>
</table>
</form>