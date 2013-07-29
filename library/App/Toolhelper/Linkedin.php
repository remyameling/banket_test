<?php

require_once APPLICATION_PATH."/../library/hyves/GenusApis.php";

class App_Toolhelper_Linkedin extends App_Toolhelper_Network
{
	protected static $_instance 	= NULL;
	protected 		 $_uniquename 	= "LINKEDIN";
	protected 		 $_displayname 	= "Linkedin";
	
	const OAUTH_CALLBACKURL = "/network/callback/name/linkedin"; 	
	const OAUTH_URL 		= "https://www.linkedin.com/uas/oauth/";
	const OAUTH_REQUESTURL  = "https://www.linkedin.com/uas/oauth/requestToken";
	const OAUTH_AUTHURL	    = "https://www.linkedin.com/uas/oauth/authorize";
	const OAUTH_ACCESSURL   = "https://www.linkedin.com/uas/oauth/accessToken";
	
	public static function getInstance(){
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Linkedin();
		}
		return self::$_instance;			
	}
	
	protected function _getConfig(){
    	if ($this->_config === NULL){
    		
    		$callBackUrl = 	"http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL;
			$this->_config = array('version'        		=> '1.0',
								   'callbackUrl' 			=> $callBackUrl,
								   'requestTokenUrl'		=> self::OAUTH_REQUESTURL,
								   'userAuthorisationUrl' 	=> self::OAUTH_AUTHURL,
								   'accessTokenUrl' 		=> self::OAUTH_ACCESSURL,
   		 					   	   'siteUrl' 				=> self::OAUTH_URL,
   							   	   'consumerKey' 			=> $this->_appKey,
   							   	   'consumerSecret' 		=> $this->_secretKey);
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
   		$token  = $this->getAccessToken();
    	$client = $token->getHttpClient($this->_getConfig());
	 
		$client->setUri('https://api.linkedin.com/uas/oauth/invalidateToken');
		$client->setMethod(Zend_Http_Client::GET);
		$response = $client->request();
		$content =  $response->getBody();
			
		parent::logOut();
    	
    }
    
    public function getFriends()
    {
    	$token  = $this->getAccessToken();
    	$client = $token->getHttpClient($this->_getConfig());
    	
    	$client->setUri('http://api.linkedin.com/v1/people/~/connections');
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('scope','self');
		$response 	= $client->request();
		$content  	= $response->getBody();
		$xml 	  	= simplexml_load_string($content);
		
		$friends 	= array();
		if (isset($xml->person))
		{
			foreach($xml->person as $person)
				$friends[] = (string) $person->{'first-name'}." ".(string) $person->{'last-name'};
				
			
		}
		
		return $friends;
    }
    
	private function createXmlShare($message)
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><share><comment>'.$message.'</comment><visibility><code>anyone</code></visibility></share>';
		return $xml;
	}
    
	public function postMessage($msgLong,$msgShort)
    {
    	$msg 	= $this->prepareMessage($msgShort,$this->_uniquename);
    	
    	$token  = $this->getAccessToken();
    	$client = $token->getHttpClient($this->_getConfig());
    	
    	$client->setUri('http://api.linkedin.com/v1/people/~/shares');
		$client->setMethod(Zend_Http_Client::POST);
		$xml = $this->createXmlShare($msg);
		
		$client->setRawData($xml,'text/xml');
		$client->setHeaders('Content-Type', 'text/xml');
		$res = $client->request();
		
		if ($res->getStatus() == 201) 
		{
			$resArr 	= $res->getHeaders();
			
			if (isset($resArr['Location']))
			{
				$location	= $resArr['Location'];
			
				$parts = explode("/",$location);
				return $parts[7];
			}
		}
		
		return NULL;
    }
    
    public function getLastpost()
    {
    	$token  = $this->getAccessToken();
    	$client = $token->getHttpClient($this->_getConfig());
    	
    	$client->setUri('http://api.linkedin.com/v1/people/~/network/updates');
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('scope','self');
		$response 	= $client->request();
		$content  	=  $response->getBody();
		$xml 	  	= simplexml_load_string($content);
		
		$status 	= array();
		if (isset($xml->update))
		{
			foreach($xml->update as $update)
				$status[] = (string) $update->{'update-content'}->person->{'current-status'};
				
			return $status[0];
		}
		
		return "";
    }
    
	public function getNumreplies($id)
    {
    	assert($id !== NULL);
    	assert($id != "");
    	
    	$token  = $this->getAccessToken();
    	$client = $token->getHttpClient($this->_getConfig());
    	
    	$client->setUri('http://api.linkedin.com/v1/people/~/network/updates');
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('scope','self');
		$client->setParameterGet('type','SHAR');
		$response 	= $client->request();
		$content  	= $response->getBody();
		
		$xml 	  	= simplexml_load_string($content);
    	
    	$status 	= array();
		if (isset($xml->update))
		{
			foreach($xml->update as $update)
				$status[(string) $update->{'update-content'}->person->{'current-share'}->id] = (string) $update->{'num-likes'};
				
			if (isset($status[$id]))
				return $status[$id];
			else
				return -1;
		}
    	else
    		return 0;
    }
}