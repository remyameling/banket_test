<?php

require_once "BaseController.php";

class Admin_LoginController extends Admin_BaseController
{
	private $_Form = NULL;
	protected $_logGroup 	= "ADMIN";
	
	public function init()
	{
		parent::init();					
							
		$this->_helper->layout->setLayout('adminlogin');
    }
    
	protected function _getForm($form=NULL)
    {
		if (NULL === $this->_Form)
		{
			$this->_Form = new RAC_Component_Form(Zend_Registry::getInstance()->admin_forms->login,"default");
		}
		return $this->_Form;
	}
	
	private function handleLoginForm($data)
	{
		// Get our authentication adapter and check credentials
		
		$adapter  = $this->getAuthAdapter($data);
		$auth     = App_Auth_Auth::getInstance();
						
		$result   = $auth->authenticate($adapter);
		
		if ($result->GetCode() === Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID)
		{
			$this->view->invalid_credentials = true;
		}
		else
		{
			$this->view->invalid_credentials = false;
		}
		
		$messages = $result->getMessages();
		
		
		
		if (!$result->isValid()) {
			// Invalid credentials
			
			$this->view->errormessages = $messages[0];			
			$this->LogError("Inloggen mislukt: invalid credentials");
			
			return false;
			
		}
		else
		{
			$userName	= $auth->getIdentity();
			$userModel 	= new App_Model_User();
			$userData   = $userModel->fetchByUserName($userName);
			
			// set date last login
						
			$userData['user_previouslogin'] = $userData['user_lastlogin_raw'];
			$userData['user_lastlogin'] 	= date("Y-m-d H:i:s");			
			$userData['user_numlogins'] 	= $userData['user_numlogins']+1;
			
			// check if admin or root logged in
			
			if ((App_Auth_Auth::getInstance()->getRole() == 'root') ||
				(App_Auth_Auth::getInstance()->getRole() == 'admin')){

					Zend_Registry::getInstance()->session->inline_edit_mode = true;	// set inline edit flag
					
			}
			
			// check validity application
		
			if ((strtotime(date("Y-m-d"))-strtotime(Zend_Registry::getInstance()->site->licence->until) <= 0) ||
				(App_Auth_Auth::getInstance()->getRole() == 'root'))
			{
				
				$userModel->update($userData['id'],$userData);			
				return true;
			}
			else
			{
				$this->LogError("Inloggen mislukt: licentie is verlopen");
				App_Auth_Auth::getInstance()->clearIdentity();
			}			
		}				
	}

    public function indexAction()
    {
        $request  = $this->getRequest();
        $form     = $this->_getForm();
		$login    = $request->getParam('login',NULL);
		
		if ($login == 'failed')
			$this->view->invalid_credentials = true;
        
        if ($this->getRequest()->isPost()){
        	 if ($form->isValid($request->getPost())) {
        	 	if ($this->handleLoginForm($form->getValues()))
        	 	{
        	 		$this->_helper->redirector->setGotoUrl('/admin/index');	
        	 		return;
        	 		
        	 	}
        	 	else
        	 		$this->_helper->redirector->setGotoUrl('/admin/login/index/login/failed');		 	
        	 }
        	 else
        	 	$this->view->invalid_credentials = true;
        }
		
        $this->view->application_name    = Zend_Registry::getInstance()->consts->application_name;
        $this->view->application_version = Zend_Registry::getInstance()->consts->application_version;	
		$this->view->form = $form;
    }
    
	public function logoutAction()
    {
        App_Auth_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index','index','default'); // back to home page
    }
}