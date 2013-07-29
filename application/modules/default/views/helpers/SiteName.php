<?php

class Default_View_Helper_SiteName extends Zend_View_Helper_Abstract
{
	public function SiteName()
   	{
		return Zend_Registry::getInstance()->db_settings->website_name;        

   	}
}