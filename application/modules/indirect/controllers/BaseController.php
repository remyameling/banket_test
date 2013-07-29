<?php

require_once APPLICATION_PATH.Zend_Registry::getInstance()->paths->components."BaseController.php";

class Indirect_BaseController extends RACCMS_Component_BaseController
{
	protected $_form 			= NULL;
	protected $_redirector  	= NULL;
	
		
	public function init()
	{
    	$this->_redirector 		= $this->_helper->getHelper('Redirector');	
    	    		
        return parent::init();        
    }
    
	protected function _getForm($form="add")
    {
		if (NULL === $this->_form){
			
			$config = Zend_Registry::getInstance()->indirect_forms->get(strtolower($this->_domain_name));
			
			if ($config === NULL)
				throw new Exception("BaseController::_getForm(".$form."): geen formulier entry gevonden voor ".$this->_domain_name." in forms.ini");
			
			assert($config !== NULL);
			
			$this->_form = new RAC_Component_Form($config,$form);
		}
		return $this->_form;
	}
	
	
	
    
	
}