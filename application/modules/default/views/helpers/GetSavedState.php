<?php

class Default_View_Helper_GetSavedState extends Zend_View_Helper_Abstract
{
	public function GetSavedState($name,$default)
   	{
   		
   		
		$session = new Zend_Session_Namespace("savestate");
    	
    	if (isset($session->$name))
    	{
    		$value = $session->$name;
    		//Zend_Registry::getInstance()->logger->log("GetSavedState name=$name; value=$value",Zend_Log::DEBUG);
    	}
    	else
    	{
    		//Zend_Registry::getInstance()->logger->log("GetSavedState name not found in session, returning default=".$default,Zend_Log::DEBUG);
    		$value = $default;
    	}
    	
    	return $value;

   	}
}