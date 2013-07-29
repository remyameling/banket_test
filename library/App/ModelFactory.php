<?php

class App_ModelFactory
{
	static 	$_instance 	= NULL;	
	static  $_models 	= array();
	
	
	
	public static function register($name,$model){
		
		
		self::$_models[$name] = $model;
	}
	
	public static function getModel($name)
	{
		if (!isset(self::$_models[$name]))		// indien nog niet reregistreerd
		{
			$mdl_name = "App_Model_".$name;		// maak model aan
			$mdl      = new $mdl_name;				
			self::register($name,$mdl);			// en registreer
		}
		
		return self::$_models[$name];
		
	}
}