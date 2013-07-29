<?php

require_once APPLICATION_PATH."/../library/facebook/src/facebook.php";

class App_Toolhelper_Facebook extends App_Toolhelper_Network
{
	protected static $_instance 	= NULL;
	protected 		 $_uniquename 	= "FACEBOOK";
	protected 		 $_displayname 	= "Facebook";
	
	const OAUTH_CALLBACKURL 	= "/network/callback/name/facebook"; 
	
	public static function getInstance(){
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Facebook();
		}
		return self::$_instance;			
	}
	
	protected function _getConfig()
    {
    	if ($this->_config === NULL)	{
    		$fbconfig['appid' ]  = $this->_appKey;
	    	$fbconfig['secret']  = $this->_secretKey;
	    	
    		$this->_config = array('appId' 			=> $fbconfig['appid'],
   		 					   	   'secret' 		=> $fbconfig['secret'],
   							   	   'cookie' 		=> true);
    	}
    	
    	return $this->_config;
    }
    
	protected function _createConsumer(){
    	return new Facebook($this->_getConfig());
    }
    
    public function logIn()
    {
    	$helper = new Zend_Controller_Action_Helper_Redirector();    	
    	
    	return $helper->gotoUrl($this->getConsumer()->getLoginUrl(array('scope'=>'publish_stream,read_stream',
    																	'redirect_uri'=>"http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL)));
    }
    
	public function logOut()
    {
    	
   		$helper = new Zend_Controller_Action_Helper_Redirector(); 

   		$url = $this->getConsumer()->getLogoutUrl(array('next'=>"http://".$_SERVER['HTTP_HOST']));
   		$this->getConsumer()->clearAllPersistentData();
   		
   		parent::logOut();
   		
   		return $helper->gotoUrl();
    }
    
	public function getFriends()
    {
    	$friendsLists = $this->getConsumer()->api('/me/friends');
    	$friends = array();
    	
    	if (isset($friendsLists['data'])){
	    	foreach($friendsLists['data'] as $friend)
	    		$friends[] = $friend['name'];
    	}
    	return $friends;    	
    }
    
	public function postMessage($msgLong,$msgShort)
    {
    	//die($msgLong);
    	$msg 	= $this->prepareMessage($msgLong,$this->_uniquename);
    	    	
    	try{
			$statusUpdate = $this->getConsumer()->api('/me/feed', 'post', array('message'=>$msg,'cb'=>''));			
			return $statusUpdate['id'];
        }
        catch (FacebookApiException $e)
        {
        	$this->_lastError = $e->getMessage();
	        return false;
        }
    }
    
	public function getLastpost()
    {
    	if ($this->isLoggedIn())
    	{
	    	try{
	    		$user_id = $this->getConsumer()->getUser();
				$result = $this->getConsumer()->api('/me/posts','get',array('access_token'=>$this->getConsumer()->getAccessToken()));
				
				if (isset($result['data'][0]['message']))
					return $result['data'][0]['message'];
		    	else
		    		return "";
	        }
	        catch (FacebookApiException $e)
	        {
	        	$this->_resetTokens();
	        	$this->_lastError = $e->getMessage();
		        return false;
	        }
    	}
    	else
    		return "";
        
    }
    
	public function getNumreplies($id)
    {
    	if ($this->isLoggedIn())
    	{
	    	try{
	    		$user_id = $this->getConsumer()->getUser();
				$result  = $this->getConsumer()->api("/$id",'get',array('access_token'=>$this->getConsumer()->getAccessToken()));
				
				//print_r($result);
				//die();
				 
				if (isset($result['likes']['count']))
					return $result['likes']['count'];
		    	else
		    		return 0;
	        }
	        catch (FacebookApiException $e)
	        {
	        	die($e->getMessage());
	        	$this->_lastError = $e->getMessage();
		        return false;
	        }
    	}
    	else
    		return "";
    }
}