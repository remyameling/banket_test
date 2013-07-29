<?php

abstract class App_Toolhelper_Network extends App_Toolhelper_Abstract
{
	abstract public		function getFriends();
    abstract public		function getLastPost();
    abstract public		function postMessage($msgLong,$msgShort);
    abstract public 	function getNumreplies($id);
   
	protected function prepareMessage($msg,$network_uniquename)
    {
    	$mdl 	= new App_Model_Network();
    	$data	= $mdl->fetchEntryByUniqueName($network_uniquename);
    	assert(!empty($data));
    	
    	$msg 	= str_replace("%member_profile_url%","%membernetwork_url_".$data['id']."%",$msg);
    	$msg 	= str_replace("%member_profile_name%","%membernetwork_username_".$data['id']."%",$msg);
    	$msg    = App_MessageHelper::getInstance()->prepareMsg($msg,true,false);
    		
    	$msg = strip_tags($msg);
    	return $msg;
    	
    }
}