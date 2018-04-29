<?php
$repository_id = '';
$repository_name = '';
$repository_enabled = 0;
$repository_endpoint_url = '';
$repository_username = '';
$repository_password = '';
$login_link='';
$clear_link='';

switch($template_mode)
{
    case 'add':
        $page_title = __('Add repository', 'installer');
        $button_caption = __('Add repository', 'installer');
        $form_action = admin_url().'admin.php?wprc_c=repositories&wprc_action=addRepository';
        break;

    case 'edit':
        $page_title = __('Edit repository', 'installer');
        $button_caption = __('Save changes', 'installer');
        $form_action = admin_url().'admin.php?wprc_c=repositories&wprc_action=updateRepository';

$nonce_login = wp_create_nonce('installer-login-link');
$nonce_clear = wp_create_nonce('installer-clear-link');
if ($repository->repository_username=='' && $repository->repository_password=='')
    $login_link='<a class="thickbox button-primary" title="' . __('Log in', 'installer') . '" href="'.admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' .$repository->id.'&amp;_wpnonce='.$nonce_login).'">'.__('Log in', 'installer').'</a>';
else
    $clear_link='<a onclick="return wprc.repositories.clearLoginInfo(this,\''.$repository->repository_name.'\');" class="button-primary" href="'.admin_url('admin.php?wprc_c=repositories&amp;wprc_action=clearLoginInfo&amp;repository_id='.$repository->id.'&amp;_wpnonce='.$nonce_clear).'">'.__('Reset Username and Password','installer').'</a>';
                
        $repository_id = $repository->id;
        $repository_name = $repository->repository_name;
        $repository_endpoint_url = $repository->repository_endpoint_url;
        $repository_enabled = $repository->repository_enabled;
        $repository_username = $repository->repository_username;
        $repository_password = $repository->repository_password;
        break;
}

?>

<script language="javascript">
jQuery(document).ready(function()
{
    wprc.repositories.renderExtensionTypes('#extension_types',<?php echo $json_types; ?>);
});
</script>
        
<div class="wrap">
    <h2><?php echo $page_title; ?></h2><br />
    <p>
    </p>
    <form method="post" action="<?php echo $form_action; ?>" id="repositories_form">
        <table class="form-table">
        <tr>
            <th><?php echo __('Repository name*', 'installer'); ?></th>
            <td>
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-repositories-form'); ?>" />
                <input type="hidden" value="<?php echo $repository_id; ?>" name="repository_id">
                <input type="text" style="width:100%;" value="<?php echo $repository_name; ?>" name="repository_name">
            </td>
        </tr>
        
        <tr>
            <th><?php echo __('Repository end point url*', 'installer'); ?></th>
            <td><input type="text" style="width:100%;" value="<?php echo $repository_endpoint_url; ?>" name="repository_endpoint_url"></td>
        </tr>
        
        <tr>
            <th><?php echo __('Repository types', 'installer'); ?></th>
            <td>
                <div id="extension_types"></div>
            </td>
        </tr>
        
        <tr>
            <th><?php echo __('Repository enabled', 'installer'); ?></th>
            <td><input type="checkbox" name="repository_enabled" value="<?php $repository_enabled; ?>" <?php if($repository_enabled) { echo 'checked="checked"'; } ?>></td>
        </tr>
        
        <tr>
        <td colspan="2">
        <p class="submit"><input type="button" onclick="wprc.repositories.validateForm('repositories_form')" value="<?php echo $button_caption; ?>" class="button-primary"></p>
        </td>
        </tr>
        </table>
    </form>
<?php if ($template_mode=='edit') : ?>
        <br />
        <table class="form-table">
        <tr>
            <th><?php echo __('User name', 'installer'); ?></th>
            <td><span style='color:gray;'><?php echo $repository_username; ?></span></td>
        </tr>
        
        <tr>
            <th><?php echo __('Password', 'installer'); ?></th>
            <td><span style='color:gray;'><?php if ($repository_password<>'') _e('Encrypted and Saved','installer'); ?></span></td>
        </tr>
        
        <tr>
        <td colspan="2">
        <div class='wprc-loader' style="display:none"></div>
        <br />
        <?php
        if ($repository_username=='' && $repository_password=='')
            echo $login_link;
        else
            echo $clear_link;
        ?>
        </td>
        </tr>
        </table>
<?php endif; ?>
</div>