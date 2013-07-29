<?php

require_once APPLICATION_PATH.Zend_Registry::getInstance()->paths->components."BaseController.php";

class BaseController extends RACCMS_Component_BaseController
{
	protected $_form = NULL;
	
	protected function initLayout($layout_file,$layout_directory)
	{
    	if (!file_exists(realpath($layout_directory)."/".$layout_file.".phtml"))
    		die("RACCMS_Component_BaseController::initLayout(): Layout file bestaat niet: ".realpath($layout_directory)."/$layout_file.phtml");
    	
		$this->_helper->layout->setLayout($layout_file)
							  ->setLayoutPath(realpath($layout_directory));		
    }
	
	protected function _getTemplateBase(){
		
		return (APPLICATION_PATH."/".
    			Zend_Registry::getInstance()->paths->templates_base.
    			WEBSITE."/");
	}
	
	public function init(){
    	
        return parent::init();
        
    }
    
	protected function _ReturnReferal()
    {
		$referal 		    = $_SERVER['HTTP_REFERER'];
		$this->_redirector  = $this->_helper->getHelper('Redirector');
		
                
        return $this->_redirector->gotoUrl($referal);
    }
    
	protected function _getForm($form="add")
    {
		if (NULL === $this->_form){
			
			$config = Zend_Registry::getInstance()->default_forms->get(strtolower($this->_domain_name));
			
			if ($config === NULL)
				throw new Exception("BaseController::_getForm(".$form."): geen formulier entry gevonden voor ".$this->_domain_name." in forms.ini");
			
			assert($config !== NULL);
			
			$this->_form = new RAC_Component_Form($config,$form);
		}
		return $this->_form;
	}
	
	
	protected function _getCurrentMemberData()
	{
		$memberMdl  = new App_Model_Member();
    	$memberData = $memberMdl->fetchByUserId($this->_getCurrentUserId());
    	if (isset($memberData['id']))
    		return $memberData;
    	else
    		return NULL;
    }
	
	protected function _getCurrentMemberId()
	{
		$data = $this->_getCurrentMemberData();
		if ($data !== NULL)
			return $data['id'];
		else
			return NULL;
    }
    
	protected function _getMemberData()
	{
		$memberMdl   = new App_Model_Member();
		
		if ($this->_getRole() == 'friends')		// if current_user is a friend
		{
			$friendMdl   = new App_Model_Friend();
			$friend_data = $friendMdl->fetchEntry($this->_getCurrentUserId());
			assert(!empty($friend_data));
			$member_data = $memberMdl->fetchEntry($friend_data['member_id']);
		}
		else
			$member_data = $memberMdl->fetchByUserId($this->_getCurrentUserId());
			
		assert(!empty($member_data));
		return $member_data;
	}
	
	protected function _print_r($data,$bDie=true){

		echo "<pre>";
		print_r($data);
		echo "</pre>";
	
		if ($bDie)
			die("===================== DIED ===================");
	}
}