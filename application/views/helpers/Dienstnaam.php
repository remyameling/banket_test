<?php

class App_View_Helper_Dienstnaam extends Zend_View_Helper_Abstract
{
	private $_siteName = NULL;
	
	protected function getSiteName()
	{
		if ($this->_siteName === NULL)
		{
			$currentIdId        = App_Auth_Auth::getInstance()->getIdentityId();
			assert($currentIdId !== NULL);
			$data 				= App_ModelFactory::getModel("user")->fetchEntry($currentIdId);
	   		$site_id			= $data['user_site_id'];   			
	   		$this->_siteName  	= Zend_Registry::getInstance()->sites->get($site_id);
		}   
				
   		return $this->_siteName;
	}
	
	public function Dienstnaam($id,$site_name=NULL)
   	{
   		if ($site_name === NULL)
   			$site_name = $this->getSiteName();
   			
   		
   		return Zend_Registry::getInstance()->sites->dienst->get($site_name)->naam->get((int)$id);
   	}
}

?>