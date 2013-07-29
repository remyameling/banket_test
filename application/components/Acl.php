<?php

class RACCMS_Component_Acl extends Zend_Acl
{

	private function Log($msg,$group="ACL",$prio=Zend_Log::DEBUG){
    	
    	if (Zend_Registry::getInstance()->logging->groups->get($group)){
    		
    		$function = "";
    		$class    = "";
    		
    		if (Zend_Registry::getInstance()->logging->logcaller){
    	
	    		$trace=debug_backtrace();
			
				$caller=array_shift($trace);
				$caller=array_shift($trace);
				$caller=array_shift($trace);
	
				$function = $caller['function'];
				if (isset($caller['class']))
					$class = $caller['class']."::";
    		}
				
			Zend_Registry::getInstance()->logger->log($group.":".$class.$function." ".$msg,$prio);
    	}    	
    }


	public function add($resource){
		$this->Log("add resource ".$resource->getResourceId());
		parent::add($resource);
	}
	
	public function getResourceString($class_name,$object_name){
		switch ($class_name){
			case "weblog":
				return "weblog_".$object_name;
			case "form":
				return "form_".$object_name;
			case "calendar":
				return "calendar_".$object_name;
			case "default":
				return "default_".$object_name;
			case "account":
				return "account_".$object_name;
			default:
				throw new Exception("RACCMS_Component_Acl::getResourceString($class_name,$object_name): class_name niet bekenend");
		}
	}
	
	public function addRole($role,$parent_role=NULL){
		
		if ($parent_role !== NULL){			
			$this->Log("add role ".$role->getRoleId()." parents: ".$parent_role[0]);
		}
		else
			$this->Log("add role ".$role->getRoleId());
			
		parent::addRole($role,$parent_role);
	}
	
	public function allow($role,$resource=NULL,$action=NULL){
		$this->Log("allow $role,$resource,$action");
		parent::allow($role,$resource,$action);
	}
	
	public function deny($role,$resource=NULL,$action=NULL){
		$this->Log("deny $role,$resource,$action");
		parent::deny($role,$resource,$action);
	}
	
	private function _initDBResources(){		
		$this->Log("ACL: _initDBResources ");
		
		$w_model   		= new App_Model_Webloggroup();
		$f_model		= new App_Model_Formgroup();
		$c_model		= new App_Model_Calendargroup();
		
		$wl_resources 	= $w_model->fetchResources();
		$f_resources 	= $f_model->fetchResources();
		$c_resources	= $c_model->fetchResources();
				
		// add resources
		foreach($wl_resources as $entry){
			$this->add(new Zend_Acl_Resource($this->getResourceString("weblog",$entry['object_name'])));
		}
		
		// add form resources
		foreach($f_resources as $entry){
			$this->add(new Zend_Acl_Resource($this->getResourceString("form",$entry['object_name'])));
		}
		
		// add calendar resources
		foreach($c_resources as $entry){
			$this->add(new Zend_Acl_Resource($this->getResourceString("calendar",$entry['object_name'])));
		}
				
	}
	
	private function _initDBRoles(){
		$this->Log("ACL: _initDBRoles ");
		
		$model     		= new App_Model_Group();	
		$roles 			= $model->fetchEntries("group_parent_id","asc");
		
		// add roles
		foreach($roles as $entry)
		{
			if ($entry['group_system'] != '1'){
				if ($entry['parent_name'] != '')
					$parent_role = array($entry['parent_name']);
				else
					$parent_role = NULL;
				
				$this->addRole(new Zend_Acl_Role($entry['group_uniquename']),$parent_role);
			}
		}		
		
		
	}
	
	
	
	private function _initConfigResources($aclconfig){		
		$this->Log("_initConfigResources");
		
		$res = $aclconfig->resources;
		
		// add resources
		foreach($res as $entry){
			$this->add(new Zend_Acl_Resource($entry));
		}				
	}
	
	private function _initConfigRoles($aclconfig){
		$this->Log("_initConfigRoles ");
		
		$roles = $aclconfig->roles;
		
		// add roles
		foreach($roles as $entry)
		{
			if ($entry->parent != '')
			{
				$parent_role = array($entry->parent);
				
			}
			else
			{
				$parent_role = NULL;
			}
				
			
			$this->addRole(new Zend_Acl_Role($entry->name),$parent_role);
		}		
	}
	
	private function _initConfigPermissions($aclconfig){
		$this->Log("_initConfigPermissions ");
		
		if (isset($aclconfig->allow))
		{
			$roles = array_keys($aclconfig->allow->toArray());				
			foreach($roles as $role){			
				$resources = array_keys($aclconfig->allow->get($role)->toArray());
				foreach($resources as $resource){				
					$actions = $aclconfig->allow->get($role)->get($resource)->toArray();

					foreach($actions as $action){

						if ($action == "*")
							$this->allow($role,$resource);
						else
							$this->allow($role,$resource,$action);
					}				
				}
			}
		}
		
		if (isset($aclconfig->deny))
		{
			$roles = array_keys($aclconfig->deny->toArray());				
			foreach($roles as $role){			
				$resources = array_keys($aclconfig->deny->get($role)->toArray());
				foreach($resources as $resource){				
					$actions = $aclconfig->deny->get($role)->get($resource)->toArray();

					foreach($actions as $action){

						if ($action == "*")
							$this->deny($role,$resource);
						else
							$this->deny($role,$resource,$action);
					}				
				}
			}
		}
	}
	
	private function _initDbPermissions(){
		$this->Log("_initDbPermissions");
		
		$wl_model  		= new App_Model_Webloggroup();		
		$wl_entries 	= $wl_model->fetchAcls();
		
		foreach($wl_entries as $entry){			
			if ($entry['read'] == 1)
				$this->allow($entry['group_name'],$this->getResourceString("weblog",$entry['object_name']),'read');
			else
				$this->deny($entry['group_name'],$this->getResourceString("weblog",$entry['object_name']),'read');
				
			if ($entry['create'] == 1)
				$this->allow($entry['group_name'],$this->getResourceString("weblog",$entry['object_name']),'create');
			if ($entry['update'] == 1)
				$this->allow($entry['group_name'],$this->getResourceString("weblog",$entry['object_name']),'update');
			if ($entry['delete'] == 1)
				$this->allow($entry['group_name'],$this->getResourceString("weblog",$entry['object_name']),'delete');
				
		}
		
		$f_model  	= new App_Model_Formgroup();		
		$f_entries 	= $f_model->fetchAcls();
		
		foreach($f_entries as $entry){			
			if ($entry['read'] == 1)
				$this->allow($entry['group_name'],$this->getResourceString("form",$entry['form_name']),'read');
			else
				$this->deny($entry['group_name'],$this->getResourceString("form",$entry['form_name']),'read');			
		}
		
		$c_model  	= new App_Model_Calendargroup();		
		$c_entries 	= $c_model->fetchAcls();
		
		foreach($c_entries as $entry){			
			if ($entry['read'] == 1)
				$this->allow($entry['group_name'],$this->getResourceString("calendar",$entry['calendar_uniquename']),'read');
			else
				$this->deny($entry['group_name'],$this->getResourceString("calendar",$entry['calendar_uniquename']),'read');			
		}
		
	}
	
    public function __construct($auth,$acl_roles,$acl_resources,$default_permissions,$site_permissions)
    {
    	//$this->_initDBResources();
    	$this->_initConfigRoles($acl_roles);
    	//$this->_initDBRoles();
    	$this->_initConfigResources($acl_resources);    	
    	$this->_initConfigPermissions($default_permissions);
    	$this->_initConfigPermissions($site_permissions);
    	//$this->_initDbPermissions();
    	
    	// Root privileges
        $this->allow('root'); 											// unrestricted access

        
    }
}