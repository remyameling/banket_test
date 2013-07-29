<?php

require_once "BaseController.php";

class ErrorsController extends BaseController
{
	public function init()
    {
    	
        $this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
    	
        return parent::init();
    }
    
	public function errorAction()
    {
    	
    	$errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
				break;
            default:
                // application error 
                
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }
    
	public function nopageAction()
    {
    	
    	$this->view->module 	= $this->getRequest()->getParam('module');
        $this->view->controller = $this->getRequest()->getParam('controller');
        $this->view->action 	= $this->getRequest()->getParam('action');
        $this->view->msg 		= $this->getRequest()->getParam('msg',NULL);
        $this->view->role 		= App_Auth_Auth::getInstance()->getRole();
        
    }
    
	public function deniedAction()
    {
    	
    	$this->view->module 	= $this->getRequest()->getParam('module');
        $this->view->controller = $this->getRequest()->getParam('controller');
        $this->view->action 	= $this->getRequest()->getParam('action');
        $this->view->role 		= App_Auth_Auth::getInstance()->getRole();
        
        
    }


}