<?php 

class Admin_View_Helper_Allowed
{
   private $_model = NULL; 
	
	
   public function setView(Zend_View_Interface $view)
   {
        $this->view = $view;
   }
   
   private function _getModel(){
   		if ($this->_model === NULL){
   			$this->_model = new App_Model_User();
   		}
   		return $this->_model;
   }
   
   public function Allowed($resource,$action)
   {
   		
   	
   		$role      = App_Auth_Auth::getInstance()->getRole();
   		
   		//die("is allowed $role,$resource,$action");
   		
   		if (Zend_Registry::getInstance()->acl->isAllowed($role,$resource,$action))
   			return true;
   		else
   			return false;
   }
}