<?php

define('WRAP_CONSENT_URL', 			'https://consent.live.com/Connect.aspx');
define('WRAP_ACCESS_URL', 			'https://consent.live.com/AccessToken.aspx');
define('WRAP_REFRESH_URL', 			'https://consent.live.com/RefreshToken.aspx');

define('REST_API_GET', 				2);
define('REST_API_POST', 			3);
define('ENDPOINT_REST_API', 		'https://apis.live.net/');
define('ENDPOINT_REST_API_VERSION', 'v5.0/');
define('PARAM_ACCESS_TOKEN', 		'?access_token=');

define('REST_PATH_APPLICATIONS', 	'applications');
define('REST_PATH_ALBUMS', 			'albums');
define('REST_PATH_COMMENTS', 		'comments');
define('REST_PATH_CONTACTS', 		'contacts');
define('REST_PATH_EVENTS', 			'events');
define('REST_PATH_FILES', 			'files');
define('REST_PATH_FRIENDS', 		'friends');
define('REST_PATH_ME', 				'me/');
define('REST_PATH_SHARE', 			'share');
define('REST_PATH_TAGS', 			'tags');




require_once APPLICATION_PATH."/../library/windowslive/lib/logic/OAuthHandler.php";

class App_Toolhelper_Windowslive extends App_Toolhelper_Contacts
{
	protected static $_instance 	= NULL;
	protected 		 $_uniquename 	= "WINDOWSLIVE";
	protected 		 $_displayname 	= "Windows Live";
	
	const OAUTH_CALLBACKURL 		= "/windowslive/callback";
	
	const WINDOWSLIVE		= "WINDOWSLIVE";
	const WL_REQUESTURL  	= WRAP_CONSENT_URL;
	const WL_SCOPE		 	= "wl.basic,wl.emails,wl.contacts_emails"; 
	
	public static function getInstance(){
		if (empty(self::$_instance)){
			self::$_instance = new App_Toolhelper_Windowslive();
		}
		return self::$_instance;			
	}
	
	protected function _getConfig()	{
		if ($this->_config === NULL){
    		
    		$consentCallBackUrl 	= "http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL;
    		$accessCallBackUrl 		= "http://".$_SERVER['HTTP_HOST'].self::OAUTH_CALLBACKURL;
    		
    		$this->_config  = array('consentAppId' 		 => $this->_appKey,
    								'accessAppSecret' 	 => $this->_secretKey,
    								'consentCallbackUrl' => $consentCallBackUrl,
    							    'accessCallbackUrl'  => $accessCallBackUrl,
    								'consentOptions'	 => self::WL_SCOPE,
    								'requestURL'		 => self::WL_REQUESTURL);    		
    	}    	
    	return $this->_config;
    }
    
    public function getConfig()
    {
    	return $this->_getConfig();
    }
    
	protected function _createConsumer(){
		
		$config  		= $this->_getConfig();
		$wrapper 		= new OAuthHandler();
		
		$appId 			= $wrapper->getReturnedParameter('consentAppId', 		$config['consentAppId']);
    	$appSecret 		= $wrapper->getReturnedParameter('accessAppSecret', 	$config['accessAppSecret']);
    	$callbackUrl 	= $wrapper->getReturnedParameter('consentCallbackUrl', 	$config['consentCallbackUrl']);
    	$options 		= $wrapper->getReturnedParameter('consentOptions', 		$config['consentOptions']);
		
		
		return $wrapper;
    }
    
    
	public function setAccessToken($request_data){
    	$token = $this->getConsumer()->parsePOSTResponse($_SERVER['REQUEST_URI']);
    	if (isset($token['wrap_verification_code']))
    	{
    		// get access token
    		
    		$token = $this->getConsumer()->getAuthorizationToken($this->getConfig(),
    															 $token['wrap_verification_code']);
    																	   
    		$this->_session->access_token = serialize($token);
    	}
    }
    
    public function logIn()
    {
    	// fetch a request token
    	$config	    = $this->_getConfig();
    	$options 	= $this->getConsumer()->getReturnedParameter('consentOptions',$config['consentOptions']);
		
    	$this->getConsumer()->getConsentToken($config,$options);		
    }
    
	public function getContacts()
    {
    	$token      = $this->getAccessToken();
    	
    	$client = new Zend_Http_Client();
		
    	$client->setUri(ENDPOINT_REST_API.ENDPOINT_REST_API_VERSION.REST_PATH_ME.REST_PATH_CONTACTS);
    	$client->setParameterGet('access_token', $token['wrap_access_token']);
    	
    	$response 	= $client->request();
    	$content  	= $response->getBody();
    	$friends    = Zend_Json::decode($content);
    	$contacts	= array();
    	
    	
    	if (!isset($friends['error']))
    	{
	    	if (!empty($friends)){
		    	foreach($friends['data'] as $friend){
		    		
		    		$email_address    = (string) $friend['emails']['preferred'];
    				$name			  = (string) $friend['name'];
    				$contacts[]		  = array('name'=>$name,'email'=>$email_address);
		    	}
	    	}
    	}
    	else
    		throw new Exception($friends['error']['message']);
    		
    	return $contacts;
    } 
}