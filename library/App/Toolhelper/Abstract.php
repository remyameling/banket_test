<?php

abstract class App_Toolhelper_Abstract
{
	protected $_appKey 		= NULL;
	protected $_secretKey 	= NULL;
	protected $_session		= NULL;	
	protected $_consumer 	= NULL;
	protected $_config	 	= NULL;
	protected $_lastError   = "";
	protected $_uniquename	= "";
	protected $_displayname	= "";
	
	
    abstract protected 	function _getConfig();
    abstract protected 	function _createConsumer();
    
    abstract public 	function logIn();
        
    public function __construct()
    {
    	$this->_session	= new Zend_Session_Namespace($this->_uniquename);
    }
    
    public function getName()
    {
    	return $this->_displayname;
    }
    
    public function init($key,$secret)
    {
    	$this->_appKey 		= $key;
    	$this->_secretKey 	= $secret;
    }
    
    public function getConsumer()
	{
    	if ($this->_consumer === NULL)
    	{
    		$this->_consumer = $this->_createConsumer();
    	}
    	return $this->_consumer;		
    }
    
	protected function setRequestToken($token){
    	$this->_session->request_token = $token;
    }
    
	protected function getRequestToken(){
    	return unserialize($this->_session->request_token);
    }
    
    public function setAccessToken($request_data){
    	$request_token = $this->getRequestToken();    	
    	$access_token  = $this->getConsumer()->getAccessToken($request_data,$request_token);  
    	
    	$this->_session->access_token = serialize($access_token);
		unset($this->_session->request_token);
    }
    
	protected function getAccessToken(){
    	if (isset($this->_session->access_token))
    		return unserialize($this->_session->access_token);
    	else
    		return NULL;   	
    }
    
    protected function _resetTokens(){
    	unset($this->_session->access_token);
    	unset($this->_session->request_token);
    }
    
	public function getLastError(){
    	return $this->_lastError;
    }
    
	public function isLoggedIn(){
		
		if ($this->getAccessToken() === NULL)
    		return false;
    	else
    		return true;
	}
	
	public function logOut(){
		$this->_resetTokens();
	}
    
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