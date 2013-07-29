<?php

class App_Toolhelper_Factory
{
	protected static $_instance = NULL;
	
	const TWITTER 			= 'TWITTER';
	const HYVES   			= 'HYVES';
	const LINKEDIN 			= 'LINKEDIN';
	const FACEBOOK  		= 'FACEBOOK';
	const WINDOWSLIVE 		= 'LINKEDIN';
	const GOOGLECONTACTS  	= 'GOOGLECONTACTS';
	
	public static function getInstance()
	{
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Factory();
		}
			
		return self::$_instance;			
	}
    
    public function getToolHelper($name)
    {
    	switch($name){
    		case self::TWITTER: 		return App_Toolhelper_Twitter::getInstance();
    		case self::HYVES:			return App_Toolhelper_Hyves::getInstance();
    		case self::LINKEDIN:		return App_Toolhelper_Linkedin::getInstance();
    		case self::FACEBOOK:		return App_Toolhelper_Facebook::getInstance();
    		case self::WINDOWSLIVE:		return App_Toolhelper_Windowslive::getInstance();
    		case self::GOOGLECONTACTS:	return App_Toolhelper_Googlecontacts::getInstance();
    		default:					throw new Exception("Netwerk $name niet bekend");
    									return NULL;
    	}   
    }
    
    public function getNetworkHelperNames(){
    	return array(self::TWITTER,self::HYVES,self::LINKEDIN,self::FACEBOOK);
    }
}