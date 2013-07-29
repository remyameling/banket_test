<?php

class RAC_Component_Pluginauth extends Zend_Controller_Plugin_Abstract
{
    private $_auth;
    private $_acl;

	protected $_s401Page;	// not authroized
	protected $_s404Page;	// not found

    public function __construct($auth, $acl)
    {
        $this->_auth 	= $auth;
        $this->_acl 	= $acl;		
	}
	
	public function set401Page($action, $controller = 'error', $module = null)
    {
        $this->_s401Page = array('module' => $module, 
                                  'controller' => $controller,
                                  'action' => $action);
    }
    
	public function set404Page($action, $controller = 'error', $module = null)
    {
        $this->_s404Page = array('module' => $module, 
                                  'controller' => $controller,
                                  'action' => $action);
    }
	
	private function _getRole()
	{
		$role = $this->_auth->getRole();
		return $role;
	}

	public function preDispatch($request)
	{
		
		$front = Zend_Controller_Front::getInstance();
		
		if ($front->getDispatcher()->isDispatchable($request))
		{			
			
			$role 		= $this->_getRole();	
			
			
			
			$controller = $request->controller;
			$action 	= $request->action;
			$module 	= $request->module;
			
			$resource 	= $module."_".$controller;
			
			
					   
			if (!$this->_acl->has($resource)) {
				$resource = null;			
			}
			
			if (!$this->_acl->isAllowed($role, $resource, $action))
			{
				Zend_Registry::getInstance()->logger->log("ACL: not allowed: $role $resource $action", Zend_Log::NOTICE);
				
				$this->_request->setModuleName($this->_s401Page['module']);
	        	$this->_request->setControllerName($this->_s401Page['controller']);
	        	$this->_request->setActionName($this->_s401Page['action']);														
			}

			
			
		}
		else
		{
			$this->_request->setModuleName($this->_s404Page['module']);
	        $this->_request->setControllerName($this->_s404Page['controller']);
	        $this->_request->setActionName($this->_s404Page['action']);
		}		
	}
}