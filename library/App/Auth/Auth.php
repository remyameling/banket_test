<?php

class App_Auth_Auth extends Zend_Auth
{
	private $_mapping = array();
	
	public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
	public function getIdentity()
    {
        $identity = parent::getIdentity();
        
        if (substr($identity,0,7) == "friend_")
        	$identity = substr($identity,7,strlen($identity));
        else
        	$identity = substr($identity,7,strlen($identity));
        
        return $identity;
    }

    public function getRole()
	{
		$role = 'guest';
		if ($this->hasIdentity())
		{
				$identity = parent::getIdentity();
				if (substr($identity,0,7) == "friend_")
        			return "friends";
        		else
        		{
					$userName	= $this->getIdentity();
					$userModel 	= new App_Model_User();
					$role	    = $userModel->fetchRoleByUserName($userName);	
					
					if ($role === NULL)
						$role = 'guest';
					else
						$role = $role['role'];
        		}								
		}		
		
		return $role;
	}

	public function getIdentityAlias()
	{
		if ($this->hasIdentity())
		{
			if ($this->getRole() != 'friends')
			{
				$userName	= $this->getIdentity();
				$userModel 	= new App_Model_User();
				$data	    = $userModel->fetchByUserName($userName);	
			
				if ($data === NULL)
					return "";
				else
					return $data['user_alias'];
			}
			else
				return "";					
		}
		return "";
	}
	
	private function fetchUserId($name)
	{
		if (!isset($this->_mapping[$name])){
			
			
			
			
			$userModel 	= new App_Model_User();
			$data	    = $userModel->fetchByUserName($name);

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
?>