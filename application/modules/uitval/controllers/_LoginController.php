<?php

require_once "BaseController.php";

class LoginController extends BaseController 
{
	private 	$_loginForm 	= NULL;
	private 	$_resetForm 	= NULL;
	private     $_identifyForm  = NULL;
	protected 	$_domain_name 	= "login";
	
	public function init(){ 

    	$this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
    	
        return parent::init();
    }
    
	protected function _getFormConfig(){
		return Zend_Registry::getInstance()->default_forms;
	}
	
	private function _getLoginForm(){
	
		if (NULL === $this->_loginForm)
		{
			$this->_loginForm = $this->_getForm('default');
			
			$this->_loginForm->setDecorators(array('FormElements',
											  array('HtmlTag', 
											  array('tag' => 'dl', 'class' => 'zend_form')),
											  array('Description', array('placement' => 'append')),'Form')
									    );
		}
		return $this->_loginForm;
	}
	
	private function _getIdentifyForm(){
	
		if (NULL === $this->_identifyForm)
		{
			$this->_identifyForm = $this->_getForm('identify');
			
			$this->_identifyForm->setDecorators(array('FormElements',
											  array('HtmlTag', 
											  array('tag' => 'dl', 'class' => 'zend_form')),
											  array('Description', array('placement' => 'append')),'Form')
									    );
		}
		return $this->_identifyForm;
	}
	
	private function _getResetForm(){
	
		if (NULL === $this->_resetForm)
		{
			$this->_resetForm = new LW_Form(Zend_Registry::getInstance()->forms->default->get("login"),"reset");
			
			$this->_resetForm->setDecorators(array('FormElements',
											  array('HtmlTag', 
											  array('tag' => 'dl', 'class' => 'zend_form')),
											  array('Description', array('placement' => 'append')),'Form')
									    );
		}
		return $this->_resetForm;
	}
	
	protected function _handleLogin($form)
	{
		// Get our authentication adapter and check credentials
        $adapter  = $this->getAuthAdapter($form->getValues());
		$auth     = App_Auth_Auth::getInstance();
								
		$result   = $auth->authenticate($adapter);				
		$messages = $result->getMessages();
		
		if (!$result->isValid())
		{
			// Invalid credentials
			$form->setDescription($messages[0]);
		}
		else
		{
			$this->_helper->getHelper('Redirector')->gotoSimple('index','index','default');	
		}
	} 
	
	public function indexAction() 
	{
		$form = $this->_getLoginForm();
				
		$ret  = $this->_handleForm($form,NULL,"_handleLogin");
	}
	
	protected function _handleIdentify($form)
	{
		// Get our authentication adapter and check credentials
		
		$data     			= $form->getValues();
		$userdata 			= App_ModelFactory::getModel("User")->fetchEntry($data['userid']);
		$group_members 		= App_ModelFactory::getModel("Group")->fetchEntryByUniqueName("members");

		if ($userdata['group_id'] == $group_members['id'])
		{
			$data['username'] = $userdata['user_name'];
			$data['password'] = $userdata['user_password'];
			
			$adapter  = $this->getAuthAdapter($data);
			$auth     = App_Auth_Auth::getInstance();
									
			$result   = $auth->authenticate($adapter);				
			$messages = $result->getMessages();
			
			if (!$result->isValid())
			{
				// Invalid credentials
				$form->setDescription($messages[0]);
			}
			else
			{
				$this->_helper->getHelper('Redirector')->gotoSimple('index','index','default');	
			}
		}
		else
			$form->setDescription("Deze user moet verplicht inloggen; alleen aanmelden is niet toegestaan");
	} 
	
	public function identifyAction()
	{
		$form 			= $this->_getIdentifyForm();
		
		$group_data 	= App_ModelFactory::getModel("Group")->fetchEntryByUniqueName("members");
		$members		= App_ModelFactory::getModel("User")->fetchByGroup($group_data['id']);
		$data		 	= array();
		
		foreach($members as $member)
			$data[$member['id']] = $member['user_alias'];
			
		$form->getElement('userid')->setMultiOptions($data);
		
		$ret  = $this->_handleForm($form,NULL,"_handleIdentify");
	}
	
	
	
	public function logoutAction()
    {
        App_Auth_Auth::getInstance()->clearIdentity();
        
        $this->_helper->getHelper('Redirector')
					  ->gotoSimple('index','index','default');
		
    }
	
	public function resetAction()
	{
		$form 		= $this->_getResetForm();
						
		if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
			
				$data = $form->getValues();
				
				// check if e-mail address exists
				$model = Zend_Registry::getInstance()->modelFactory->getModel("User");
				if ($model->exists($data['username']))
				{
					$data = $model->fetchByUserName($data['username']);
				
					// reset user's password
					
					$data['user_active']   = 0;
					$data['user_hash']     = substr(md5(uniqid(rand(), true)),0,32);
					$data['user_password'] = substr(md5(uniqid(rand(), true)),0,8);
					
					$model->update($data['id'],$data);
					
					$mail_data['user_name'] 	= $data['user_name'];
					$mail_data['id'] 			= $data['id'];
					$mail_data['user_hash'] 	= $data['user_hash'];
					$mail_data['user_password'] = $data['user_password'];
					$mail_data['domain'] 		= "http://".$_SERVER['HTTP_HOST'];
					
					// send reset mail
					$mailSender = new LW_Mailsender();
					$mailSender->sendMail(Zend_Registry::getInstance()->mail->reset_password,$mail_data);								
										
					// display reset page
					$this->render('resetok');				
				}					
				else
				{
					$this->render('onbekend');								
				}
		    }
        }
		
		$this->view->form = $form;
	}		
}