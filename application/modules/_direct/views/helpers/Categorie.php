<?php

class Stilstand_View_Helper_Categorie extends Zend_View_Helper_Abstract
{
	protected function getSiteName()
	{
		$data 		= App_ModelFactory::getModel("user")->fetchEntry(App_Auth_Auth::getInstance()->getIdentityId());
   		$site_id	= $data['user_site_id'];   			
   		$site_name  = Zend_Registry::getInstance()->sites->get($site_id);
   		
   		return $site_name;
	}
	
	public function Categorie($category_id,$site_name=NULL)
   	{
   		if ($site_name === NULL)
   			$site_name = $this->getSiteName();   		
   		
   		return Zend_Registry::getInstance()->stilstand_consts->cat->get($site_name)->get($category_id);
   	}
}

?>