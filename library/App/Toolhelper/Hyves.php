<?php

require_once APPLICATION_PATH."/../library/hyves/GenusApis.php";

class App_Toolhelper_Hyves extends App_Toolhelper_Network
{
	protected static $_instance 	= NULL;
	protected 		 $_uniquename 	= "HYVES";	
	protected 		 $_displayname 	= "Hyves";
	
	const OAUTH_CALLBACKURL 	= "/network/callback/name/hyves";	
	const HA_VERSION = "1.2.1";
	
	public static function getInstance(){
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Hyves();
		}
		return self::$_instance;			
	}
	
	protected function _getConfig(){
    	if ($this->_config === NULL){
    		$this->_config = array();
    	}
    	
    	return $this->_config;
    }
	
    protected function _createConsumer(){
    	try{
    		$oConsumer = new OAuthConsumer($this->_appKey,$this->_secretKey);
    		$consumer  = new GenusApis($oConsumer, self::HA_VERSION);
    	}
    	catch(GeneralException $e)
		{
			echo "General Exception occured:<br>Code: ".$e->getCode()."<br>Message: ".$e->getMessage();
		}
		catch(HyvesApiException $e)
		{
			echo "HyvesApi Exception occured:<br>Code: ".$e->getCode()."<br>Message: ".$e->getMessage();
		}
		
		return $consumer;
    }
    
    public function setAccessToken($request_data)
    {
    	$request_token = $this->getRequestToken();
		$access_token  = $this->getConsumer()->retrieveAccesstoken($request_token);
    	
    	$this->_session->access_token = serialize($access_token);		
    }
    
    public function logIn()
    {
    	$oRequestToken 	= $this->getConsumer()
    						   ->retrieveRequesttoken(array("friends.get", 
    						   								"users.get",
    						   								"albums.getByUser",
    						   								"wwws.create","wwws.getByUser","wwws.getRespects"));
    	
    	$this->setRequestToken(serialize($oRequestToken));
    						   
    	$callBackUrl 	= "http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL;
    	
        $this->getConsumer()->redirectToAuthorizeUrl($oRequestToken, $callBackUrl);    	
    }
    
	public function getFriends()
    {
    	$token 		= $this->getAccessToken();
    	$friends 	= $this->getConsumer()->doMethod("friends.get", array(), $token);
    	
    	$ids = "";
    	foreach($friends->userid as $userid)
    		$ids = $ids.((string) $userid[0]).",";
    	$ids = substr($ids,0,strlen($ids)-1);
    	
    	$users 		= $this->getConsumer()->doMethod("users.get", array("userid"=>$ids), $token);
    	
    	$usernames  = array();
    	foreach($users->user as $user)
    		$usernames[]  = $user->firstname." ".$user->lastname." (".$user->url.")";
    		
    	return $usernames;
    }
    
	public function postMessage($msgLong,$msgShort)
    {
    	$msg 		= $this->prepareMessage($msgLong,$this->_uniquename);
    	
    	$token 		= $this->getAccessToken();
    	$result     = $this->getConsumer()->doMethod("wwws.create",array('visibility'=>'friends_of_friends','emotion'=>$msg,'where'=>'MijnToolbox'), $token);
    	
    	if (isset($result->www->wwwid))
    		return $result->www->wwwid;
    	else
    	{
    		return false;
    	}    	
    }
    
	public function getLastpost()
    {
    	$token 		= $this->getAccessToken();
    	$result     = $this->getConsumer()->doMethod("wwws.getByUser",array('userid'=>$token->getUserid()), $token);
    	
    	
    	if (isset($result->www))
    		return (string) $result->www->emotion;
    	else
    	{
    		return false;
    	}    	
    }
    
	public function getNumreplies($id)
    {
    	$token 		= $this->getAccessToken();
    	
    	try
    	{
    		$result     = $this->getConsumer()->doMethod("wwws.getRespects",array('target_wwwid'=>$id), $token);
    		if (isset($result->info))
    		{
	    		return (string) $result->info->totalresults;
    		}
	    	else
	    	{
	    		return false;
	    	}  
    	}
   		catch(GeneralException $e)
		{
		
			echo "General Exception occured:<br>Code: ".$e->getCode()."<br>Message: ".$e->getMessage();
		}
		catch(HyvesApiException $e)
		{
			echo "HyvesApi Exception occured:<br>Code: ".$e->getCode()."<br>Message: ".$e->getMessage();
			$this->_resetTokens();
			
		}    	
		return 0;  	
    }
}