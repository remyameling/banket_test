<?php

require_once "BaseController.php";

class IndexController extends BaseController
{
	protected $_logGroup = "CONTENT";
	protected $_model = NULL;
	protected $_domain_name = "index";
	
	public function preDispatch()
    {
    }
	
    public function init()
    { 
    	$this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
						  
        return parent::init();
    }
    
    
    public function indexAction()
    {
    		
    	
    }
}