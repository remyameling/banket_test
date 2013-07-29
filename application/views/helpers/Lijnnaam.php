<?php

class App_View_Helper_Lijnnaam extends Zend_View_Helper_Abstract
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
	
	public function Lijnnaam($lijnnr,$site_name=NULL)
   	{
   		if ($site_name === NULL)
   			$site_name = $this->getSiteName();
   			
   		$lijnen = Zend_Registry::getInstance()->sites->lijn->get($site_name);
   		assert($lijnen !== NULL);
   			
   		$lijn   = $lijnen->get((int)$lijnnr);
   		if ($lijn !== NULL)
   			return $lijn->naam;
   		else
   			return "";
   	}
}

?>