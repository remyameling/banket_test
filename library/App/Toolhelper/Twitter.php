<?php

class App_Toolhelper_Twitter extends App_Toolhelper_Network
{
	protected static $_instance 	= NULL;
	protected 		 $_uniquename 	= "TWITTER";
	protected 		 $_displayname 	= "Twitter";
	
	const OAUTH_CALLBACKURL 	= "/network/callback/name/twitter";
	const OAUTH_URL 			= "http://twitter.com/oauth";
	
	public static function getInstance(){
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Twitter();
		}
		return self::$_instance;			
	}
	
	protected function _getConfig(){
    	if ($this->_config === NULL){
    		
    		$callBackUrl = 	"http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL;
			$this->_config = array('callbackUrl' 	=> $callBackUrl,
   		 					   	   'siteUrl' 		=> self::OAUTH_URL,
   							   	   'consumerKey' 	=> $this->_appKey,
   							   	   'consumerSecret' => $this->_secretKey);
    	}    	
    	return $this->_config;
    }
	
	protected function _createConsumer(){
    	return new Zend_Oauth_Consumer($this->_getConfig());
    }
    
	public function logIn()
    {
    	// fetch a request token
		$token = $this->getConsumer()->getRequestToken();
		
		// set session
		$this->setRequestToken(serialize($token));
		
		// redirect
		$this->getConsumer()->redirect();
    }
    
    public function logOut()
    {
    	$twitter = new Zend_Service_Twitter(array('username' => 'johndoe',
   												  'accessToken' => $this->getAccessToken()));
   		$response   = $twitter->account->endSession();
   		
   		parent::logOut();
    }
    
	public function getFriends()
    {
    	$token 		= $this->getAccessToken();
		
    	$twitter 	= new Zend_Service_Twitter(array('username' => 'huizenverkoper','accessToken' => $token));
		$response   = $twitter->user->followers();
		
		$arr = array();
		if (isset($response->user)){
			foreach($response->user as $user)
				$arr[] = (string) $user->name." (".(string) $user->screen_name.")";
		}
		
		
		return $arr;
    }
    
    public function postMessage($msgLong,$msgShort)
    {
    	$msg 	= $this->prepareMessage($msgShort,$this->_uniquename);
    	    	
    	$token 	= $this->getAccessToken();
		$client = $token->getHttpClient($this->_getConfig());
		
		$client->setUri('http://twitter.com/statuses/update.json');
		$client->setMethod(Zend_Http_Client::POST);
		$client->setParameterPost('status', $msg);
		
		$response = $client->request();
		
		$data = Zend_Json::decode($response->getBody());
		$result = $response->getBody();
		
		if (isset($data['text']))
		{
			return $data['id_str'];
		}
		else
		{
			$this->_lastError = $data['error'];
			return false;
		}
    }
    
	public function getLastpost()
    {
    	$token 	= $this->getAccessToken();
		
    	$twitter 	= new Zend_Service_Twitter(array('username' => '','accessToken' => $token));
		$response   = $twitter->status->userTimeline();
		
		$arr = array();
		if (isset($response->status)){
			foreach($response->status as $stat)
				$arr[] = (string) $stat->text;
		}
		
		if (isset($arr[0]))
			return $arr[0];
		else
			return "";
    }
    
	public function getNumreplies($id)
    {
    	assert($id !== NULL);
    	assert($id != "");
    	
    	$token 	= $this->getAccessToken();
		
    	$twitter 	= new Zend_Service_Twitter(array('username' => '','accessToken' => $token));
		$response   = $twitter->status->userTimeline();
		
		$arr = array();
		if (isset($response->status)){
			foreach($response->status as $stat)
				$arr[(string) $stat->id] = (string) $stat->retweet_count;
		}
		
		if (isset($arr[$id]))
			return $arr[$id];
		else
			return 0;
    }
}