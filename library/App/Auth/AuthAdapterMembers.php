<?php

class App_Auth_AuthAdapterMembers extends Zend_Auth_Adapter_DbTable
{
	private static $_instance = NULL;
	
	public function __construct($adapter,$tableName,$identityColumn,$credentialColumn,$credentialTreatment){
		return parent::__construct($adapter,$tableName,$identityColumn,$credentialColumn,$credentialTreatment);
	}
	
	public function authenticate()
	{
		$result 	= parent::authenticate();		
		$identity 	= "member_".$this->_identity;		
		
		switch($result->getCode())
		{
			case Zend_Auth_Result::SUCCESS:
				return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,$identity);
				break;
			case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,$identity,array("Ongeldig username/wachtwoord"));
				break;
			case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,$identity,array("Gebruiker is onbekend"));
				break;
			default:
				$messages = $result->getMessages();
				throw new Exception("Unhandeled result with message: ".$messages[0]." code = ".$result->getCode());
				break;
		}
	}
}

?>