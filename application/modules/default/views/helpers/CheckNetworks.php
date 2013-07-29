<?php

class Default_View_Helper_CheckNetworks extends Zend_View_Helper_Abstract
{
	public function CheckNetworks($member_id)
   	{
		/* 
		   controleer of de member met dit id is aangemeld bij de netwerken 
		   die deze user in zijn profiel heeft aangevinkt 
		*/
   		
        $model 		= new App_Model_Membernetwork();
        $networks 	= $model->fetchByMember($member_id);
        $logged_in  = true;
        
        foreach($networks as $network){
        	$network_name = strtoupper($network['network_name']);        	
        	$helper       = App_Toolhelper_Factory::getInstance()->getToolHelper($network_name);        	
        	$logged_in    = $logged_in && $helper->isLoggedIn();
        	
        }
           
       	return $logged_in;

   	}
}