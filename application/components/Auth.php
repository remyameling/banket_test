<?php

class RAC_Component_Auth1 extends Zend_Auth
{
	private $_mapping = array();
	
	public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getRole()
	{
		$role = 'guest';
		if ($this->hasIdentity())
		{
			$userName	= $this->getIdentity();
			
			$userModel 	= new App_Model_User();
			$role	    = $userModel->fetchRoleByUserName($userName);	
			
			if ($role === NULL)
				$role = 'guest';
			else
				$role = $role['role'];					
		}
		return $role;
	}

	public function getIdentityAlias()
	{
		if ($this->hasIdentity())
		{
			$userName	= $this->getIdentity();
			$userModel 	= new App_Model_User();
			$data	    = $userModel->fetchByUserName($userName);	
			
			if ($data === NULL)
				return "";
			else
				return $data['user_alias'];					
		}
		return "";
	}
	
	private function fetchUserId($name)
	{
		if (!isset($this->_mapping[$name])){
			
			$userModel 	= new App_Model_User();
			$data	    = $userModel->fetchByUserName($userName);	
			
			if ($data === NULL)
				$this->_mapping[$name] = "";
			else
				$this->_mapping[$name] = $data['id'];			
		}
		
		return $this->_mapping[$name];
	}
	
	public function getIdentityId()
	{
		if ($this->hasIdentity())
		{
			$userName	= $this->getIdentity();
			return $this->fetchUserId($userName);
								
		}
		return "";
	}
	
	public function clearIdentity()
    {
    	
        return parent::clearIdentity();	
    }
}