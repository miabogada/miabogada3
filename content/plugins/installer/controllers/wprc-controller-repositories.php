<?php
class WPRC_Controller_Repositories extends WPRC_Controller
{
    public function addRepository($get, $post)
    {
        $msg=sprintf('Repositories add enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        
        $nonce=$post['_wpnonce'];
        if (! wp_verify_nonce($nonce, 'installer-repositories-form') ) die("Security check");
        
        $model = WPRC_Loader::getModel('repositories');
       
        $repository_name = $post['repository_name'];
        $repository_endpoint_url = $post['repository_endpoint_url'];
        $enabled = $post['repository_enabled'];
        //$deleted = $post['repository_deleted'];
        $user_name = '';//$post['repository_username'];
        $password = '';//$post['repository_password'];
        $repository_types = $post['repository_types'];
       
        if(isset($enabled))
        {
           $repository_enabled = 1;
        }
        else
        {
            $repository_enabled = 0;
        }

        // check is https provided
        WPRC_Loader::includeSiteEnvironment();
        $params = '';
        if(!WPRC_SiteEnvironment::checkSslProvidingByUrl($repository_endpoint_url))
        {
            $params = '&warning=https_not_provided';
            $repository_enabled = 0;
        }

        $model->addRepository($repository_name, $repository_endpoint_url, $user_name, $password, $repository_enabled, $repository_types);
       
        $msg=sprintf('Repositories add complete');
        WPRC_Functions::log($msg,'controller','controller.log');
        $this->redirectToRepositoriesPage($params);
    } 
    
    public function updateRepository($get, $post)
    {
        $msg=sprintf('Repositories update enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        
        $nonce=$post['_wpnonce'];
        if (! wp_verify_nonce($nonce, 'installer-repositories-form') ) die("Security check");
        
        $model = WPRC_Loader::getModel('repositories');
       
        $repository_id = $post['repository_id'];
        $repository_name = $post['repository_name'];
        $repository_endpoint_url = $post['repository_endpoint_url'];
        $enabled = isset($post['repository_enabled'])?1:0;
        //$deleted = isset($post['repository_deleted'])?$post['repository_deleted']:0;
        //$user_name = $post['repository_username'];
        //$password = $post['repository_password'];
        $repository_types = $post['repository_types'];
       
        $repository_enabled=$enabled;
		/*if(isset($enabled))
        {
            $repository_enabled = 1;
        }
        else
        {
            $repository_enabled = 0;
        }*/

        // check is https provided
        WPRC_Loader::includeSiteEnvironment();
        $params = '';
        if(!WPRC_SiteEnvironment::checkSslProvidingByUrl($repository_endpoint_url))
        {
            $params = '&warning=https_not_provided';
            $repository_enabled = 0;
        }

        $model->updateRepositoryNoLogin($repository_id, $repository_name, $repository_endpoint_url, $repository_enabled, $repository_types /*, $repository_deleted*/);
       
		// clear cache
		$rmcache = WPRC_Loader::getModel('cached-requests');
		$rmcache->cleanCache();
		// clear updates
		delete_site_transient( 'update_themes' );
		delete_site_transient( 'update_plugins' );
		
        $msg=sprintf('Repositories update complete');
        WPRC_Functions::log($msg,'controller','controller.log');
        $this->redirectToRepositoriesPage($params);
    }
    
    public function clearLoginInfo($get, $post)
    {
        $msg=sprintf('Repositories clear login enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        if (isset($get['repository_id']))
        {
            $nonce=$get['_wpnonce'];
            if (! wp_verify_nonce($nonce, 'installer-clear-link') ) die("Security check");
            
            $model = WPRC_Loader::getModel('repositories');
            $result = $model->clearLogin(intval($get['repository_id']));
            if ($result)
            {
                // clear cache
                $rmcache = WPRC_Loader::getModel('cached-requests');
                $rmcache->cleanCache();
                // clear updates
                delete_site_transient( 'update_themes' );
                delete_site_transient( 'update_plugins' );
            }
            echo json_encode(array('result'=>$result));
        }
        $msg=sprintf('Repositories clear login completed');
        WPRC_Functions::log($msg,'controller','controller.log');
        exit;
    }
    
    public function updateExtensionMap($get, $post)
    {
        if (isset($get['update_extension_map']))
        {
            $msg=sprintf('Extension maps update entered');
            WPRC_Functions::log($msg,'controller','controller.log');
            // clear cache
            delete_transient( 'wprc_update_extensions_maps' );
            $result = WPRC_Installer::wprc_update_extensions_maps();
            echo json_encode(array('result'=>$result));
            $msg=sprintf('Extension maps update complete');
            WPRC_Functions::log($msg,'controller','controller.log');
            exit;
        }
        exit;
    }
    
    public function redirectToRepositoriesPage($params = '')
    {
        $url = admin_url().'admin.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php'.$params;
        
        header("location: $url");
    }
    
    public function test()
    {
       // echo ABSPATH.'wp-admin/includes/template.php';
       // require_once(ABSPATH.'wp-admin/includes/screen.php');
       // require_once(ABSPATH.'wp-admin/includes/plugin.php');
       // require_once(ABSPATH.'wp-admin/includes/template.php');
       // WPRC_Loader::includeAdminTop();
        
        echo 'test';
    }
}
?>