<?php

class RAC_Component_AuthAdapter extends Zend_Auth_Adapter_DbTable
{
	private static $_instance = NULL;
	
	public function __construct($adapter,$tableName,$identityColumn,$credentialColumn,$credentialTreatment){
		return parent::__construct($adapter,$tableName,$identityColumn,$credentialColumn,$credentialTreatment);
	}
	
	public static function getInstance()
	{
		if (empty(self::$_instance))
		{
			self::$_instance = new LW_AuthAdapter();
		}
		return self::$_instance;			
	}	 
	 
	public function authenticate()
	{
		$result = parent::authenticate();

		switch($result->getCode())
		{
			case Zend_Auth_Result::SUCCESS:
				return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,$this->_identity);
				break;
			case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,$this->_identity,array("Ongeldig username/wachtwoord"));
				break;
			case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,$this->_identity,array("Gebruiker is onbekend"));
				break;
			default:
				$messages = $result->getMessages();
				throw new Exception("Unhandeled result with message: ".$messages[0]." code = ".$result->getCode());
				break;
		}
	}
}