<?php
class WPRC_Controller_RepositoryReporter extends WPRC_Controller
{
	public function sendUninstallReport($get, $post)
    {
        $msg=sprintf('Repository Reporter uninstall report enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        
        $nonce=$post['_wpnonce'];
        if (! wp_verify_nonce($nonce, 'installer-deactivation-form') ) die("Security check");
        
        $reporter = WPRC_Loader::getRequester('uninstall-reporter');
                    
        $uninstall_reason_report = $reporter->prepareRequest($post); 
        
        if($uninstall_reason_report)
        {
            $reporter->sendRequest($uninstall_reason_report);
        } 
        
        unset($reporter);
                    
		// redirect to the deactivation page ---------------------------------------
        //$this->redirectToDeactivationPage();
        $msg=sprintf('Repository Reporter uninstall report complete');
        WPRC_Functions::log($msg,'controller','controller.log');
        exit;
    }
    
    private function redirectToDeactivationPage()
    {
        $url = $_SERVER['HTTP_REFERER'];
                                 
        if (isset($_POST['bulk_deactivate']) && $_POST['bulk_deactivate']=='1')
		{
			header("location: $url");
		}
		else
		{
			WPRC_Loader::includeUrlAnalyzer();
			$params = WPRC_UrlAnalyzer::getExtensionFromUrl($url);
							
			if(array_key_exists('action',$params) && array_key_exists('type',$params))
			{
				if(($params['action'] == 'deactivate' || ($params['action'] == 'activate' && $params['type'] == 'theme')) && $params['type'] <> '')
				{
					header("location: $url&reported=true");
				}
			}
		}
    }    
    
    public function skipUninstallReport($get, $post)
    {
        // redirect to the deactivation page 
        $this->redirectToDeactivationPage();
    }
 // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<  TEST CALL <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< //
    public function testCall($get, $post)
    {
        echo 'Test<hr>';

        $model = WPRC_Loader::getModel('extensions');
        $tree = $model->getFullExtensionsTree();

        WPRC_Loader::includeDebug();
        //WPRC_Debug::print_r($tree, __FILE__, __LINE__);

       // $extension_path =
    }


    public function checkCompatibility($get, $post)
    {
        $msg=sprintf('Repository Reporter check compatibility enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        
        $reporter = WPRC_Loader::getRequester('compatibility-reporter');

        $check_extension_type = $get['extension_type_singular'];
        $check_extension_name = $get['extension_name'];
        $check_extension_repository_url = $get['repository_url'];
        $check_extension_version = $get['extension_version'];

        $parameters = array(
            'check_extension_name' => $check_extension_name,
            'check_extension_type' => $check_extension_type,
            'check_extension_repository_url' => $check_extension_repository_url,
            'check_extension_version' => $check_extension_version
        );

        $report = $reporter->prepareRequest($parameters);

        // send request only once even if it fails
		$response = $reporter->sendRequest($report,true);

        // layout
        if (isset($response) && isset($response->body))
		{
			$left_extensions = $response->body['left_extensions'];

			$right_extensions = array();
			if(is_array($left_extensions) && count($left_extensions)>0)
			{
				$left_extension = array_shift($left_extensions);
				$right_extensions = $left_extension['compatibility_info'];
			}

			$no_compatibility_information = false;
			if(count($right_extensions) == 0)
			{
				$no_compatibility_information = true;
			}
		}
		else
			$no_compatibility_information = true;

        require_once(WPRC_TEMPLATES_DIR.'/extension-compatibility-information.tpl.php');


        //WPRC_Loader::includePage('check-compatibility');

        $msg=sprintf('Repository Reporter check compatibility complete');
        WPRC_Functions::log($msg,'controller','controller.log');
   }
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> //    
//    public function viewPluginInfo($get, $post)
//    {        
//        WPRC_Loader::includeExtensionDataManager();
//        $all_data = WPRC_ExtensionDataManager::getAllData('extension');
//        
//        if(count($all_data)==0)
//        {
//            return false;
//        }
//        
//        foreach($all_data AS $key => $item)
//        {
//            $all_data[$key]['last_activation_date'] = date('Y-m-d H:i:s', $item['last_activation_date']);
//        }
//        echo '<pre>'; print_r($all_data); echo '</pre>';
//    }
    
//    public function testSentReportsList($get, $post)
//    {
//        $reporter = WPRC_Loader::getRequester('correct-work-reporter');
//        $list = $reporter->getSentReportsList();
//        
//        echo '<pre>BEFORE: '; print_r($list); echo '</pre>';
//        
//        // test actions: 
//        
//        //$result = $reporter->addSentReportsListItem('embed-iframe/embediframe.php');
//        $result = $reporter->isSentReportsListItemExists('embed-iframe/embediframe.php');
//        
//        echo '<br>Operation result: ';
//        if($result)
//        {
//            echo 'TRUE';
//        }
//        else
//        {
//            echo 'FALSE';
//        }
//        
//        // ----------------------
//        $list = $reporter->getSentReportsList();
//        echo '<pre>AFTER: '; print_r($list); echo '</pre>';
//    }

    public function getCorrectWorkReportStatus()
    {
        echo '<h1 align="center">getCorrectWorkReportStatus</h1>';
        
        $cwr = WPRC_Loader::getRequester('correct-work-reporter');
        $cwr->getStatus();
    }
}
?>