<?php 

class Admin_View_Helper_UserInfo
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
   
   public function UserInfo()
   {
   		$user_name = App_Auth_Auth::getInstance()->getIdentity();
   		$role      = App_Auth_Auth::getInstance()->getRole();
   		
   		return $user_name." (".$role.")";
   }
}