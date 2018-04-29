<?php

class WPRC_Controller_RepositoryLogin extends WPRC_Controller
{
    
    private $repository=null;
    private $repo_model=null;
    
    public function RepositoryLogin(){
        
        list($get, $post) = func_get_args();
    
        if(isset($get['repository_id']))
        {
            $this->repo_model = WPRC_Loader::getModel('repositories');
            $this->repository = $this->repo_model->getRepositoryByField('id', (int)$get['repository_id']);
            if(empty($get['submit']))
            {
                $nonce=$get['_wpnonce'];
                if (! wp_verify_nonce($nonce, 'installer-login-link') ) die("Security check");
                self::render();
            }
            else
            {
                $nonce=$get['_wpnonce'];
                if (! wp_verify_nonce($nonce, 'installer-login-form') ) die("Security check");
                
                $login = $this->repo_model->testLogin($get['repository_id'], $get['username'], $get['password'],(isset($get['_plain']) && $get['_plain']=='true')?true:false);
                
                if($login!=false && empty($login['error']))
                {
                    echo json_encode(array('success' => 1, 'message' => $login['message']));    
                }
                else
                {
                    echo json_encode(array('success' => 0, 'message' => $login['message']));    
                }

                exit; 
            }
        }
    }
    
    public function render(){
        include WPRC_PAGES_DIR . '/login.php';    
    }
    
}
  
?>
