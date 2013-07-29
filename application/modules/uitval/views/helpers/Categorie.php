<?php

class Uitval_View_Helper_Categorie extends Uitval_View_Helper_Uitval
{
	public function Categorie($category_id,$site_name=NULL)
   	{
   		if ($site_name === NULL)
   			$site_name = $this->getSiteName();   		
   		
   		return Zend_Registry::getInstance()->uitval_consts->cat->get($site_name)->get($category_id);
   	}
}

?>