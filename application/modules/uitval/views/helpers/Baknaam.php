<?php

class Uitval_View_Helper_Baknaam extends Uitval_View_Helper_Uitval
{
	public function Baknaam($baktype,$site_name=NULL)
   	{
   		if ($site_name === NULL)
   			$site_name = $this->getSiteName();
   			
   		return Zend_Registry::getInstance()->uitval_consts->bak->get($site_name)->get($baktype)->naam;
   	}
}

?>