<?php

class Default_View_Helper_ConfigLookup extends Zend_View_Helper_Abstract
{
	public function ConfigLookup($value,$config,$key,$subkey=NULL)
   	{
   		
   		if (isset(Zend_Registry::getInstance()->$config))
   			$config = Zend_Registry::getInstance()->$config->$key;
   		else
   			return "Default_View_Helper_ConfigLookup:: config ($config) not found";
		
		if ($config === NULL)
			return "Default_View_Helper_ConfigLookup:: config key ($key) not found";
		
		if ($subkey !== NULL)
			$config = $config->get($subkey);
			
		if ($config !== NULL)
			return $config->$value;
		else
			return "Default_View_Helper_ConfigLookup:: config subkey ($subkey) not found";        

   	}
}