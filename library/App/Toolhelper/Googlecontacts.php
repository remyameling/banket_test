<?php

class App_Toolhelper_Googlecontacts extends App_Toolhelper_Contacts
{
	protected static 			$_instance 		= NULL;
	protected 		 			$_uniquename 	= "GOOGLECONTACTS";
	protected 		 			$_displayname 	= "Google Contacts";
	
	const OAUTH_CALLBACKURL 	= "/googlecontacts/callback";	
	const OAUTH_REQUESTURL  	= "https://www.google.com/accounts/OAuthGetRequestToken";
	const OAUTH_AUTHURL	    	= "https://www.google.com/accounts/OAuthAuthorizeToken";
	const OAUTH_ACCESSURL   	= "https://www.google.com/accounts/OAuthGetAccessToken";
	
	
	public static function getInstance(){
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Googlecontacts();
		}
		return self::$_instance;			
	}
	
	protected function _getConfig()	{
    	if ($this->_config === NULL){
    		
    		$callBackUrl = 	"http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL;
			$this->_config = array('requestScheme' 			=> Zend_Oauth::REQUEST_SCHEME_HEADER,
								   'version'        		=> '1.0',
								   'signatureMethod' 		=> 'HMAC-SHA1',
								   'callbackUrl' 			=> $callBackUrl,
								   'requestTokenUrl'		=> self::OAUTH_REQUESTURL,
								   'userAuthorizationUrl' 	=> self::OAUTH_AUTHURL,
								   'accessTokenUrl' 		=> self::OAUTH_ACCESSURL,
   							   	   'consumerKey' 			=> $this->_appKey,
   							   	   'consumerSecret' 		=> $this->_secretKey
   							   	   );
    	}    	
    	return $this->_config;
    }
    
	protected function _createConsumer(){
		return new Zend_Oauth_Consumer($this->_getConfig());
    }
    
    public function logIn()
    {
    	$SCOPES = array('https://www.google.com/m8/feeds/contacts/default/full');
    	
    	// fetch a request token
		$token = $this->getConsumer()->getRequestToken(array('scope' => implode(' ', $SCOPES),'xoauth_displayname'=>'test'));
		
		// set session
		$this->setRequestToken(serialize($token));
		
		// redirect
		$this->getConsumer()->redirect();	
    }
    
	public function getContacts()
    {
    	$token  = $this->getAccessToken();
    	$client = $token->getHttpClient($this->_getConfig());
    	
    	$client->setUri('https://www.google.com/m8/feeds/contacts/default/full');
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('scope','self');
		$response 	= $client->request();
		$content  	= $response->getBody();
		$xml 	  	= simplexml_load_string($content);
		$xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
		
    	foreach ($xml->entry as $entry)
    	{
    		$attributes 	  = $entry->xpath('gd:email/@address');   		
    		$email_address    = (string) $attributes[0]->address;
    		$name			  = (string) $entry->title;
    		$contacts[]		  = array('name'=>$name,'email'=>$email_address);
    		
    		
    	}  
	
    	return $contacts;
    }
}