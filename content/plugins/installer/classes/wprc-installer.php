<?php
/**
 * MainClass
 * 
 * Main class of the plugin
 * Class encapsulates all hook handlers
 * 
 */
class WPRC_Installer
{    
/**
 * Initialize plugin enviroment
 */ 
   public static function init()
   { 
        if(is_admin())
        {    
            wp_enqueue_style('wprc_style', WPRC_ASSETS_URL.'/css/wprc.css');
            wp_enqueue_script('sprintf', WPRC_ASSETS_URL.'/js/sprintf-0.7-beta1.min.js');
            wp_enqueue_script('wprc', WPRC_ASSETS_URL.'/js/wprc.js');
     
            // include jquery UI
            wp_enqueue_script('jquery_ui', WPRC_ASSETS_URL.'/scripts/jquery_ui/jquery-ui-1.8.17.custom.min.js');
            wp_enqueue_style('jquery_ui', WPRC_ASSETS_URL.'/scripts/jquery_ui/themes/'.WPRC_JQUERY_UI_THEME.'/jquery-ui-1.8.17.custom.css');
            
            wp_enqueue_script('jquery_multiselect', WPRC_ASSETS_URL.'/scripts/multiselect/jquery.multiselect.min.js');
            wp_enqueue_style('jquery_multiselect', WPRC_ASSETS_URL.'/scripts/multiselect/css/jquery.multiselect.css');
            
			wp_enqueue_script('thickbox',null,array('jquery'));
			wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
            
            add_action('admin_print_scripts',array('WPRC_Installer', 'inlineJSSettings'));
			
            /**
			 * Print language texts for javascript
			 */ 
			//add_action('wp_print_scripts', array('WPRC_Installer', 'printJsLanguage'));

			/**
			 * Add plugin menu items
			 */ 
			//add_action('admin_menu', array('WPRC_Installer', 'addMenuItems'));
			
			// load translations from locale
			load_plugin_textdomain('installer', false, WPRC_LOCALE_FOLDER);
			
			// include router
            WPRC_Loader::includeRouter();
            WPRC_Router::execute();            
        }

       self::executeScheduledTasks();
   } 

   public static function inlineJSSettings()
   {
        $debug = (defined('WPRC_DEBUG_JS') && WPRC_DEBUG_JS)?'true':'false';
        $timeout = defined('WPRC_JS_TIMEOUT')?WPRC_JS_TIMEOUT:40000;
        
        // Translations / settings
        // insert into javascript
        wp_localize_script( 'wprc', 'wprc_config', array(
            'DEBUG' => $debug,
            'TIMEOUT' => $timeout,
            'unknown_error' => __('Unknown Error','installer'),
            'timeout_error' => __('Connection Timeout','installer'),
            'd_ext_updated_msg' => __('%d extension(s) were updated','installer'),
            'no_ext_updated_msg' => __('No extensions updated, probably up to date'),
            'login_cleared' => __('Username and Password were reset'),
            'login_cleared_failed' => __('Username and Password reset Failed'),
            'login_clear_confirm' => __('Are you sure you want to reset the login details to %s repository? Use that only when you need to change the login details'),
            ));
   }
   
   public static function executeScheduledTasks()
   {
        $cacher = WPRC_Loader::getRequestCacher();
        $cacher->cleanCache();
   }

/**
 * Set site id
 * 
 * @param int site id
 */     
    public static function setSiteId($site_id)
    {
        return update_option('wprc_site_id', $site_id);
    }
    
 /**
 * Check site id
 * If site id doens\'t exist method send request to the server for new site id
 */      
    public static function getSiteId()
    {
        $site_id = get_option('wprc_site_id');

        if($site_id)
        {
            return $site_id;    
        }
        //<<<<<<<<<<<<<<< ----------------------------------------------  
        $site_informer = WPRC_Loader::getRequester('site-informer');
        $request = $site_informer->prepareRequest(array());
            
        $response = $site_informer->sendRequest($request);        
        //---------------------------------------------- >>>>>>>>>>>>>>>>>>>> 
        if(!isset($response->site_id))
        {
            return false;
        }
          
        $new_site_id = $response->site_id;
        $res = self::setSiteId($new_site_id);
        
        if(!$res)
        {
            return false;
        }
        
        return $new_site_id;
    }
    
    
/**
 * Return current site info
 * 
 * @param bool get enviroment information only (without site id)
 */     
    public static function getSiteInfo($get_enviroment_only = false)
    {
        global $wpdb; 
        
        // get mysql version
        $row = $wpdb->get_row("SELECT VERSION() AS mysql_version");
        $mysql_version = $row->mysql_version;
       
        // get db encoding
        $mysql_var = $wpdb->get_row("SHOW VARIABLES LIKE 'character_set_database'", ARRAY_A);
        $db_encoding = $mysql_var['Value'];
       
        $wp_version = get_bloginfo('version');
        $site_url = get_option('siteurl');
        
        if($get_enviroment_only)
        {
            $site_info = array(
                'wp_version' => $wp_version,
                'db_encoding' => $db_encoding,
                'mysql_version' => $mysql_version,
                'site_url' => $site_url
            );
        }
        else
        {
            $site_id = self::getSiteId();
            
            $site_info = array(
                'id' => $site_id,
                'wp_version' => $wp_version,
                'db_encoding' => $db_encoding,
                'mysql_version' => $mysql_version,
                'site_url' => $site_url
            );
        }
        return $site_info;
    }
    
    public static function onActivation()
    {
        self::getSiteId();
        self::prepareDB();
		// clear update transients
		delete_site_transient( 'update_plugins' );
		delete_site_transient( 'update_themes' );
        delete_transient( 'wprc_update_repositories' );
        delete_transient( 'wprc_update_extensions_maps' );
		// clean cache
		$rmcache = WPRC_Loader::getModel('cached-requests');
		$rmcache->cleanCache();
        
        // clear crons
        $crons = _get_cron_array();
        foreach ($crons as $key=>$job){
            if (isset($job['wprc_schedule_update_plugins'])){
                unset($crons[$key]);
            }
            if (isset($job['wprc_schedule_update_themes'])){
                unset($crons[$key]);
            }
        }
        _set_cron_array($crons);
    }

/**
 * Prepare database of use
 */ 
    public static function prepareDB()
    {        
        $repo_model = WPRC_Loader::getModel('repositories');
        $repo_model->prepareDB();
    
        $ext_types_model = WPRC_Loader::getModel('extension-types');
        $ext_types_model->prepareDB();
        
        $repo_rel_model = WPRC_Loader::getModel('repositories-relationships');
        $repo_rel_model->prepareDB();
        
        $settings_model = WPRC_Loader::getModel('settings');
        $settings_model->prepareDB();
        
        $extensions_model = WPRC_Loader::getModel('extensions');
        $extensions_model->prepareDB();

        $extensions_model = WPRC_Loader::getModel('cached-requests');
        $extensions_model->prepareDB();
    }

/**
 * Print language texts for javascript
 */     
    public static function printJsLanguage()
    {
        if(!is_admin())
        {
            return false;
        }
        
        WPRC_Loader::includeLanguageManager();
        WPRC_LanguageManager::printJsLanguage();
    }

/**
 * Add menu items
 */     
    public static function addMenuItems()
    {
        global $_registered_pages;

        // add update bubble count to menus
		$repos_count=0;
		$current=get_transient('wprc_update_repositories');
		if (isset($current) && $current!=false && isset($current->count))
			$repos_count=$current->count;
		
		$repos_title = esc_attr( sprintf( ' Updated %d Repositories', $repos_count ) );

		//$menu_label = sprintf( __( 'Installer','installer' ), "<span class='update-plugins count-$repos_count' title='$repos_title'><span class='update-count'>" . number_format_i18n($repos_count) . "</span></span>" );
		$menu_label = sprintf( __( 'Installer','installer' ), "<span class='update-plugins count-$repos_count' title='$repos_title'><span class='update-count'>" . number_format_i18n($repos_count) . "</span></span>" );
		if ($repos_count>0)
			$submenu_label = sprintf( __( 'Repositories %s','installer' ), "<span class='update-plugins count-$repos_count' title='$repos_title'><span class='update-count'>" . number_format_i18n($repos_count) . "</span></span>" );
		else
			$submenu_label = __( 'Repositories','installer' );

		//$wprc_index_slug = WPRC_PAGES_DIR2.'/wprc-index.php';
		$wprc_index_slug = WPRC_PAGES_DIR2.'/repositories.php';
	    //add_menu_page('wprc-index.php',$menu_label,'manage_options',$wprc_index_slug,'','',70);
	    
        add_options_page($menu_label,$menu_label,'manage_options',$wprc_index_slug,'','');
        add_options_page(__( 'Uninstall Installer','installer' ), __( 'Uninstall Installer','installer' ),'manage_options',WPRC_PAGES_DIR2.'/uninstall.php','','');
        
        /*$hookname = get_plugin_page_hookname(WPRC_PAGES_DIR2.'/uninstall.php','');
        $_registered_pages[$hookname] = true;*/
        
       /*add_menu_page('wprc-index.php',$menu_label,'manage_options',$wprc_index_slug,'','');
        add_submenu_page($wprc_index_slug, $submenu_label, $submenu_label,'manage_options',WPRC_PAGES_DIR2.'/repositories.php');
        add_submenu_page($wprc_index_slug, __( 'Deleted Repositories','installer' ), __( 'Deleted Repositories','installer' ),'manage_options',WPRC_PAGES_DIR2.'/deleted-repositories.php');
        add_submenu_page($wprc_index_slug, __( 'Settings','installer' ), __( 'Settings','installer' ),'manage_options',WPRC_PAGES_DIR2.'/wprc-index.php');
        add_submenu_page($wprc_index_slug, __( 'Uninstall','installer' ), __( 'Uninstall','installer' ),'manage_options',WPRC_PAGES_DIR2.'/uninstall.php');*/
    }

/**
 * Handler of extension deactivation event
 */     
    public static function onExtensionDeactivation($extension)
    {
		if ($extension==WPRC_PLUGIN_BASENAME) return;
		
		// if bulk deactivation skip
		if (defined('WPRC_SKIP_BULK_DEACVTIVATION_REPORT') && WPRC_SKIP_BULK_DEACVTIVATION_REPORT==true)
		{
			if(array_key_exists('checked', $_POST)) return;
		}
		
		$settings_model = WPRC_Loader::getModel('settings');
        $settings = $settings_model->getSettings();
        
        if(isset($settings['allow_compatibility_reporting']) && $settings['allow_compatibility_reporting'] == 1)
        {
            WPRC_Loader::includeUrlAnalyzer();
    
            $url = $_SERVER['REQUEST_URI'];
            $url_params = WPRC_UrlAnalyzer::getUrlParams($url);
            
            if(count($url_params) > 0)
            {  
                $report_send = '';
                if(array_key_exists('reported', $url_params))
                {
                    $report_send = $url_params['reported'];
                }  
                
                $plugin = '';
                if(array_key_exists('plugin', $url_params))
                { 
                    $plugin = $url_params['plugin'];
                }
            
                if(($report_send !== 'true') && ($plugin !== WPRC_PLUGIN_NAME))
                {
                    WPRC_Loader::includeAdminTop(true);  
                    WPRC_Loader::includePage('deactivation-reason-form');
                        
                    exit;
                }
            }
			// bulk deactivation
			else if (!isset($_REQUEST['reported']))
			{
				WPRC_Loader::includeAdminTop(true);  
				WPRC_Loader::includePage('deactivation-reason-form');
					
				exit;
			}
        }
    }
    
    public static function onPluginActivation($plugin_name)
    {
        $reporter = WPRC_Loader::getRequester('correct-work-reporter');
        
        // Save list of activated plugins and last activation date, set timer for the plugin
        $reporter->initRequest($plugin_name);
    }
    
    public static function onPluginDeactivation($plugin_name)
    {
		if ($plugin_name==WPRC_PLUGIN_BASENAME) return;
		
	   $reporter = WPRC_Loader::getRequester('correct-work-reporter');
        
        // Reset timer of deactivated plugin
        $reporter->resetPluginTimer($plugin_name);
    }
    
    public static function onSwitchTheme($theme_name)
    {
        global $wp_version;
		
		$reporter = WPRC_Loader::getRequester('correct-work-reporter');

        // Reset timer of switched theme      
        $reporter->resetThemeTimer();
		
        // Save list of activated plugins and last activation date, set timer for the theme
        $reporter->initRequest($theme_name);
    }
    
    public static function wprc_upgrade_pre_install($status,$hook_extra)
    {
        $str=print_r($hook_extra,true);
        file_put_contents(dirname(__FILE__).'/upgrade_pre.txt',$str,FILE_APPEND);
        return true;
    }
    
    public static function wprc_upgrade_post_install($status,$hook_extra,$result)
    {
        $str=print_r($hook_extra,true);
        $str .= '\n'.print_r($result,true);
        file_put_contents(dirname(__FILE__).'/upgrade_post.txt',$str,FILE_APPEND);
        return true;
    }
    
    public static function onViewWPPage()
    {        
        $extension_type = 'extension';
        $reporter = WPRC_Loader::getRequester('correct-work-reporter');
         
        $data = array(
            'extension_type' => $extension_type
        );
       
        try
        { 
            $report = $reporter->prepareRequest($data);
            
            if($report)
            {
                $res = $reporter->sendRequest($report);
            }
        }
        catch(Exception $e)
        {
            WPRC_AdminNotifier::addInstantMessage('<strong>WPRC_CorrectWorkReporter '.__('Error','installer').'</strong>: '.$e->getMessage(),'error');
			//echo '<strong>WPRC_CorrectWorkReporter '.__('Error','installer').'</strong>: '.$e->getMessage();
        }
    }

/**
 * Insert plugin to the database after plugin install from the search results page
 * 
 * (on 'wprc_plugins_api_results_plugin_information' hook)
 */ 
    public static function addPlugin($result)
    {                       
        $url_action = '';   
        $repository_id = 0;
        if(array_key_exists('action', $_GET))
        {
            $url_action = $_GET['action'];
        }
        
        if(array_key_exists('repository_id', $_GET))
        {
            $repository_id = $_GET['repository_id'];
        }
                
        $selfpage=explode('/',$_SERVER['PHP_SELF']);
		$selfc=count($selfpage)-1;
		$doit=false;
		if ($selfpage[$selfc]==='update.php' && $selfpage[$selfc-1]==='wp-admin')
			$doit=true;
        //if($url_action == 'install-plugin' &&  $_SERVER['PHP_SELF'] == '/wp-admin/update.php')
		if($url_action == 'install-plugin' &&  $doit && $repository_id>0)
        {
            $plugin_slug = $result->slug;
            $plugin_name = $result->name;
        
            $ext_model = WPRC_Loader::getModel('extensions');
            //$plugin_path = $ext_model->getPluginPathByName($plugin_name); // there is no such plugin in results of get_plugins() function at this moment
            $ext_model->addExtension($plugin_name, $plugin_slug, '', 'plugins', $repository_id);       
        }
        
        return $result;
    }


/**
 * Add repository id to the input arguments for themes_api request
 * 
 * (on 'wprc_themes_api_args_theme_information' hook)
 */ 
    public static function addThemesApiArgs($args)
    {
        $doit=false;
		if (isset($_SERVER['HTTP_REFERER']) && preg_match('/\/wp-admin\/theme-install\.php/', $_SERVER['HTTP_REFERER'], $matches))
		{
			$http_referer = $_SERVER['HTTP_REFERER'];
		
			WPRC_Loader::includeUrlAnalyzer();
			$url_params = WPRC_UrlAnalyzer::getUrlParams($http_referer);
			$doit=true;
			if (!isset($url_params['theme']))
			{
				$url_params=$_GET;
			}
		}
		else
		{
			$url_params=$_GET;
			$selfpage=explode('/',$_SERVER['PHP_SELF']);
			$selfc=count($selfpage)-1;
			if ($selfpage[$selfc]==='update.php' && $selfpage[$selfc-1]==='wp-admin')
				$doit=true;
		}
		
		$action = '';
        $repository_id = '';
        if(array_key_exists('action', $_GET))
        {
            $action = $_GET['action'];
        }
    
        //if(preg_match('/\/wp-admin\/theme-install\.php/', $http_referer, $matches) && $action == 'install-theme')
        if($doit && $action == 'install-theme')
        {        
            if(array_key_exists('repository_id', $url_params))
            {
                $repository_id = $url_params['repository_id'];
                
                $theme = '';
                if(array_key_exists('theme', $url_params))
                {
                    $theme_slug = $url_params['theme'];
                }
                $args->repositories[] = $repository_id;
                
                // add theme
                $ext_model = WPRC_Loader::getModel('extensions');
                $ext_model->addExtension('',$theme_slug,'','themes',$repository_id);
            }
        }
       
        return $args;
    }

/**
 * Update extenion_path for the plugins
 * 
 * (on 'install_plugin_complete_actions' hook)
 * 
 * @param array install actions array
 */     
    public static function updatePluginExtensionPath($install_actions)
    {        
        if(array_key_exists('activate_plugin', $install_actions))
        {
            // get parameters from the GET
            $repository_id = $_GET['repository_id'];
            $plugin_slug = $_GET['plugin'];
        
            // get activation link from the html
            preg_match('/href="(?<activation_link>[^"]*)"/', $install_actions['activate_plugin'], $matches);
            $activation_link = $matches['activation_link'];
            
            // parse activation link
            WPRC_Loader::includeUrlAnalyzer();
            $url_params = WPRC_UrlAnalyzer::getUrlParams($activation_link);   
            $plugin_path = $url_params['plugin']; 
            
            // update extension
            $et_model = WPRC_Loader::getModel('extensions');
            $et_model->updateExtensionPath($plugin_path, $plugin_slug, $repository_id);
        }
        return $install_actions;
    }
   
/**
 * Update extenion_path for the themes
 * 
 * (on 'install_theme_complete_actions' hook)
 * 
 * @param array install actions array
 */     
    public static function updateThemeExtensionPath($install_actions, $api, $stylesheet, $theme_info)
    {
        // !!!!!! themes have no extension_path
        
        if(array_key_exists('activate',$install_actions))
        {
            $theme_slug = $_GET['theme'];
            
            // update extension
            $ext_model = WPRC_Loader::getModel('extensions');
            $ext_model->updateExtension(array('extension_was_installed' => 1), array('extension_slug' => $theme_slug), array('%d'), array('%s'));
        }
    
        return $install_actions;
    }    
 /**
 * Update theme after theme install from the search results page
 * 
 * (on 'wprc_themes_api_results_theme_information' hook)
 */ 
    public static function updateThemeName($result)
    {                               
        $url_action = '';   
        $repository_id = 0;
        if(array_key_exists('action', $_GET))
        {
            $url_action = $_GET['action'];
        }
                  
        $selfpage=explode('/',$_SERVER['PHP_SELF']);
		$selfc=count($selfpage)-1;
		$doit=false;
		if ($selfpage[$selfc]==='update.php' && $selfpage[$selfc-1]==='wp-admin')
			$doit=true;
        //if($url_action == 'install-theme' &&  $_SERVER['PHP_SELF'] == '/wp-admin/update.php')
        if($url_action == 'install-theme' &&  $doit)
        {
            $theme_slug = $result->slug;
            $theme_name = $result->name;
        
            $ext_model = WPRC_Loader::getModel('extensions');
            
            $data = array(
                'extension_name' => $theme_name,
                'extension_path' => $theme_slug
            );
            
            $where = array(
                'extension_slug' => $theme_slug
            );
            
            $ext_model->updateExtension($data, $where, array('%s'), array('%s'));       
        }
        
        return $result;
    }

    public function addPluginCompatibilityLink($actions, $plugin_file, $plugin_data, $context)
    {
        $name = $plugin_data['Name'];

        // get plugin data from the DB
        $extensions_model = WPRC_Loader::getModel('extensions');
        $extensions = $extensions_model->getDBExtensionsTree('plugins', true);

        $repository_endpoint_url = '';
        if(array_key_exists('plugins', $extensions))
        {
            if(array_key_exists($name, $extensions['plugins']))
            {
                $repository_endpoint_url = $extensions['plugins'][$name]['repository_endpoint_url'];
            }
        }
        $actions['check_compatibility'] = '<a href="' . self_admin_url( 'admin.php?wprc_c=repository-reporter&amp;wprc_action=checkCompatibility&amp;repository_id='.'&amp;repository_url='.$repository_endpoint_url.'&amp;extension_name=' . $name .
            '&amp;extension_version=' . $plugin_data['Version'] . '&amp;extension_type_singular=plugin&amp;extension_type=plugins&amp;TB_iframe=true&amp;width=400&amp;height=300' ) . '" class="thickbox check-compatibility" title="' .
            esc_attr( sprintf( __( 'Check compatibility status for "%s" plugin', 'installer' ), $name ) ) . '">' . __( 'Check compatibility', 'installer' ) . '</a>';;

        return $actions;
    }

    public function addThemeCompatibilityLink($actions, $theme)
    {
        $name = $theme['Name'];

        // get theme data from the DB
        $extensions_model = WPRC_Loader::getModel('extensions');
        $extensions = $extensions_model->getDBExtensionsTree('themes');

        $repository_endpoint_url = '';
        if(count($extensions)>0)
        {
            if(array_key_exists($name, $extensions['themes']))
            {
                $repository_endpoint_url = $extensions['themes'][$name]['repository_endpoint_url'];
            }
        }
        $href = self_admin_url( 'admin.php?wprc_c=repository-reporter&amp;wprc_action=checkCompatibility&amp;repository_id='.'&amp;repository_url='.$repository_endpoint_url.'&amp;extension_name=' . $name .
            '&amp;extension_version=' . $theme['Version'] . '&amp;extension_type_singular=theme&amp;extension_type=themes&amp;TB_iframe=true&amp;width=500&amp;height=300' );

        $actions['check_compatibility'] = '<br><a href="'.$href.'" class="thickbox" title="'.__('Check compatibility status for the theme', 'installer').'">'.__('Check compatibility', 'installer').'</a>';

        return $actions;
    }

    public function cacheApiRequest($server_url, $action, $args, $results)
    {
        $cacher = WPRC_Loader::getRequestCacher();
        return $cacher->cacheApiRequest($server_url, $action, $args, $results);
    }

    public function getCachedApiRequest($server_url, $action, $args)
    {
        $cacher = WPRC_Loader::getRequestCacher();
        return $cacher->getCachedApiRequest($server_url, $action, $args);
    }
	
	// plugins update functions etc..
	public static function wprc_install_plugin_information()
	{
		WPRC_Loader::includeListTable('wprc-plugin-information');
		WPRC_PluginInformation::wprc_install_plugin_information();
	}
	
	public static function wprc_update_plugins() 
	{
		WPRC_Loader::includeListTable('wprc-plugin-information');
		WPRC_PluginInformation::wprc_update_plugins();
	}
	
	public static function wprc_maybe_update_plugins() 
	{
		WPRC_Loader::includeListTable('wprc-plugin-information');
		WPRC_PluginInformation::wprc_maybe_update_plugins();
	}
	
	public static function wprc_plugin_update_rows() {
		WPRC_Loader::includeListTable('wprc-plugin-information');
		WPRC_PluginInformation::wprc_plugin_update_rows();
	}
	
	// themes update functions etc..
	public static function wprc_install_theme_information()
	{
		WPRC_Loader::includeListTable('wprc-theme-information');
		WPRC_ThemeInformation::wprc_install_theme_information();
	}
	
	public static function wprc_update_themes( ) {
		WPRC_Loader::includeListTable('wprc-theme-information');
		WPRC_ThemeInformation::wprc_update_themes();
	}
		
	public static function wprc_maybe_update_themes( ) {
		WPRC_Loader::includeListTable('wprc-theme-information');
		WPRC_ThemeInformation::wprc_maybe_update_themes();
	}
		
	public static function wprc_theme_update_rows() {
		WPRC_Loader::includeListTable('wprc-theme-information');
		WPRC_ThemeInformation::wprc_theme_update_rows();
	}

	
	public static function wprc_update_repositories( ) {
	
		WPRC_Loader::getRepositoryConnector();
		
		$url=WPRC_UPDATE_REPOS_URL;
		$timestamp=0;
		$current=get_transient( 'wprc_update_repositories');
		if (isset($current) && $current!=false)
		{
			if ( isset( $current->last_checked ))
				$timestamp=$current->last_checked;
		}
		$args=array(
			'action' => 'repositories_update',
			'request' => array('timestamp'=>$timestamp), 
		);
		$response=WPRC_RepositoryConnector::sendRequest('post',$url,$args);
		$num_repos=0;
		if (isset($response) && $response!=false && !is_wp_error($response))
		{
			if (isset($response->result))
			{
				if ($response->result=='up_to_date')
				{
					$current=new stdClass;
					$current->last_checked=time();
					$current->repos=array();
					$current->count=0;
					set_transient( 'wprc_update_repositories',$current);
					return false;
				}
				else
				{
					$repo_model = WPRC_Loader::getModel('repositories');
					$installed_repos=$repo_model->getAllRepositories();
					$repos=$response->repositories;
					$new_repos=array();
					$update_repos=array();
					$notified_repos=array();
					
					foreach ($repos as $key=>$repo)
					{
						foreach ($installed_repos as $irepo)
						{
							if ($repo->repository_url==$irepo->repository_endpoint_url)
							{
								if ($irepo->repository_deleted==0)
								{
									$temp=array(
										'repository_id'=>$irepo->id,
										'repository_name'=>$repo->repository_name,
										'repository_enabled'=>$irepo->repository_enabled,
										'plugins'=>false,
										'themes'=>false
									);
									$repo->enable_status=intval($repo->enable_status);
									$repo->repository_types=intval($repo->repository_types);
									if ($repo->enable_status==1)
									{
										$temp['repository_enabled']=1;
									}
									elseif ($repo->enable_status==2)
									{
										$temp['repository_enabled']=0;
									}
									elseif ($repo->enable_status==3)
									{
										$temp['repository_enabled']=0;
										$notified_repos[]=array('name'=>$repo->repository_name,'url'=>$repo->repository_url);
									}
									if ($repo->repository_types==1)
									{
										$temp['themes']=true;
										$temp['plugins']=false;
									}
									elseif ($repo->repository_types==2)
									{
										$temp['themes']=false;
										$temp['plugins']=true;
									}
									elseif ($repo->repository_types==3)
									{
										$temp['themes']=true;
										$temp['plugins']=true;
									}
									$update_repos[]=$temp;
								}
								unset($repos[$key]);
								break;
							}
						}
					}
					foreach ($repos as $key=>$repo)
					{
						$temp=array(
							'repository_id'=>null,
							'repository_name'=>$repo->repository_name,
							'repository_enabled'=>0,
							'repository_endpoint_url'=>$repo->repository_url,
							'plugins'=>false,
							'themes'=>false
						);
						$repo->enable_status=intval($repo->enable_status);
						$repo->repository_types=intval($repo->repository_types);
						if ($repo->enable_status==1)
						{
							$temp['repository_enabled']=1;
						}
						elseif ($repo->enable_status==2)
						{
							$temp['repository_enabled']=0;
						}
						elseif ($repo->enable_status==3)
						{
							$temp['repository_enabled']=0;
							$notified_repos[]=array('name'=>$repo->repository_name,'url'=>$repo->repository_url);
						}
						if ($repo->repository_types==1)
						{
							$temp['themes']=true;
							$temp['plugins']=false;
						}
						elseif ($repo->repository_types==2)
						{
							$temp['themes']=false;
							$temp['plugins']=true;
						}
						elseif ($repo->repository_types==3)
						{
							$temp['themes']=true;
							$temp['plugins']=true;
						}
						$new_repos[]=$temp;
						unset($repos[$key]);
					}
					$repo_model->updateUpdateRepositories($update_repos);
					$repo_model->addUpdateRepositories($new_repos);
					$num_repos=count($notified_repos);
					if ($num_repos>0)
					{
						$link='<a href="'.self_admin_url('options-general.php?page=installer/pages/repositories.php').'">'.__('Repositories Page','installer').'</a>';
						$msg="<p>".sprintf(__("%d Repositories were added/updated. Go to %s to manage them.",'installer'),$num_repos,$link)."</p>";
						WPRC_AdminNotifier::addMessage('wprc-repositories-update',$msg);
					}
					$current=new stdClass;
					$current->last_checked=time();
					$current->count=$num_repos;
					$current->repos=$notified_repos;
					set_transient( 'wprc_update_repositories', $current );
					delete_transient( 'wprc_update_extensions_maps' );
                    return true;
				}
			}
		}
	}
	
	public static function wprc_maybe_update_repositories( ) {
		$current = get_transient( 'wprc_update_repositories' );
		if (isset($current) && $current!=false)
		{
			if ( isset( $current->last_checked ) && WPRC_REPO_UPDATE_PERIOD > ( time( ) - $current->last_checked ) )
				return false;
		}
		return self::wprc_update_repositories();
        //return true;
	}	
	
	public static function wprc_update_extensions_maps( ) {
	
		WPRC_Loader::getRepositoryConnector();
		
		$url=WPRC_UPDATE_REPOS_URL;
		$timestamp=0;
		$current=get_transient( 'wprc_update_extensions_maps');
		if (isset($current) && $current!=false)
		{
			if ( isset( $current->last_checked ))
				$timestamp=$current->last_checked;
		}
		$args=array(
			'action' => 'extensions_map',
			'request' => array('timestamp'=>$timestamp), 
		);
        
        // get existing extensions list
        $em=WPRC_Loader::getModel('extensions');
        $ext=$em->getFullExtensionsTree();
        
        $extensions=array();
        $ext_map=array();
        foreach (array('plugins','themes') as $etype)
        {
            $extensions[$etype]=array();
            foreach ($ext[$etype] as $exte)
            {
                $tmp=(array)$exte;
                if (isset($tmp['Name']))
                {
                    $extensions[$etype][]=$tmp['Name'];
                    $ext_map[$etype][$tmp['Name']]=$tmp;
                }
                elseif (isset($tmp['name']))
                {
                    $extensions[$etype][]=$tmp['name'];
                    $ext_map[$etype][$tmp['name']]=$tmp;
                }
            }
            $args['request'][$etype]=$extensions[$etype];
            unset($extensions[$etype]);
            unset($ext[$etype]);
        }
		
        // send request for info
        $response=WPRC_RepositoryConnector::sendRequest('post',$url,$args);
		
		if (isset($response) && $response!=false && !is_wp_error($response))
		{
			if (isset($response->result))
			{
				if ($response->result=='up_to_date')
				{
					$current=new stdClass;
					$current->last_checked=time();
					$current->count=0;
					set_transient( 'wprc_update_extensions_maps',$current);
					return false;
				}
				else
				{
					$repo_model = WPRC_Loader::getModel('repositories');
					$installed_repos=$repo_model->getAllRepositories();
                    $repo_map=array();
                    foreach ($installed_repos as $repo)
                    {
                        $repo_map[$repo->repository_endpoint_url]=$repo->id;
                    }
                    unset($installed_repos);
                    $ext_count=0;
					foreach (array('plugins','themes') as $etype)
                    {
                        if (isset($response->$etype))
                        {
                            $tmp=(array)$response->$etype;
                            foreach ($tmp as $name=>$einfo)
                            {
                                $einfo=(array)$einfo;
                                if (isset($ext_map[$etype]) && isset($ext_map[$etype][$name]))
                                {
                                    if (isset($ext_map[$etype][$name]['repository_id']))
                                    {
                                        if (isset($repo_map[$einfo['repository_url']]))
                                        {
                                            $type_id=($einfo['extension_type']==1)?1:2;
                                            $data=array(
                                               'extension_name' => $einfo['extension_name'],
                                               'extension_type_id' => $type_id,
                                               'extension_slug' => $einfo['extension_slug'],
                                               'extension_path' => $einfo['extension_path'],
                                               'repository_id' => $repo_map[$einfo['repository_url']],
                                               'extension_was_installed' => 1,  // re-enable extension if was disabled
                                            );
                                            $format=array(
                                                '%s',
                                                '%d',
                                                '%s',
                                                '%s',
                                                '%d',
                                                '%d'
                                            );
                                            $where=array(
                                                'extension_name' => $name
                                            );
                                            $where_format=array(
                                                '%s'
                                            );
                                            $em->updateExtension($data, $where, $format, $where_format);
                                            $ext_count++;
                                        }
                                    }
                                    else
                                    {
                                         if (isset($repo_map[$einfo['repository_url']]))
                                        {
                                            $type_name=($einfo['extension_type']==1)?'plugins':'themes';
                                            $em->addExtensionInstalled($name, $einfo['extension_slug'], $einfo['extension_path'], $type_name, $repo_map[$einfo['repository_url']],1);
                                            $ext_count++;
                                        }
                                    }
                                }
                            }
                        }
                    }
					
                    $current=new stdClass;
					$current->last_checked=time();
					$current->count=$ext_count;
					set_transient( 'wprc_update_extensions_maps', $current );
                    return $ext_count;
				}
			}
		}
	}
    
    public static function wprc_maybe_update_extensions_maps( ) {
		$current = get_transient( 'wprc_update_extensions_maps' );
		if (isset($current) && $current!=false)
		{
			if ( isset( $current->last_checked ) && WPRC_REPO_UPDATE_PERIOD > ( time( ) - $current->last_checked ) )
				return false;
		}
		return self::wprc_update_extensions_maps();
        //return true;
	}

	public static function wprc_maybe_check_server()
    {
        $r1=self::wprc_maybe_update_repositories();
        $r2=self::wprc_maybe_update_extensions_maps();
        if ($r1 || $r2!=false) // anything changed?
        {
            // clear updates
            delete_site_transient( 'update_plugins');
            delete_site_transient( 'update_themes');
        }
         
    }
}
?>