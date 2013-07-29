<?php

class Stilstand_View_Helper_Subcategorie extends Zend_View_Helper_Abstract
{
	protected function getSiteName()
	{
		$data 		= App_ModelFactory::getModel("user")->fetchEntry(App_Auth_Auth::getInstance()->getIdentityId());
   		$site_id	= $data['user_site_id'];   			
   		$site_name  = Zend_Registry::getInstance()->sites->get($site_id);
   		
   		return $site_name;
	}
	
	public function Subcategorie($subcategory_id,$site_name=NULL)
   	{
   		if ($site_name === NULL)
   			$site_name = $this->getSiteName();   		
   		
   		return Zend_Registry::getInstance()->stilstand_consts->subcat->get($site_name)->get($subcategory_id);
   	}
}

?>