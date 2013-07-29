<?php

class App_WizardHelper
{
	const LABEL 				= "label";
	const VIEWSCRIPT		 	= "viewscript";
	const HANDLER			 	= "handler";
	
	private   $_wizardData 		= NULL;
	private	  $_cancelUrl		= "";
	private   $_initalHandler   = NULL;
	private   $_finalHandler	= NULL;
	private   $_session			= NULL;
	private	  $_data			= array();
	private   $_currentStep		= -1;
	
	public function __construct($uname,$cancelUrl){
		$this->_cancelUrl = $cancelUrl;
		$this->_session   = new Zend_Session_Namespace($uname);
	}
	
	public function __set($name, $value)
    {
        $this->_session->$name = $value;
    }

    public function __get($name)
    {
        if ($this->_session->__isset($name))
            return $this->_session->__get($name);
        else
			return null;
    }
    
    public function __unset($name){
    	unset($this->_session->$name);
    }
    
	public function AddInitial($handler)
    {
    	$this->_initalHandler = $handler;
    }
	
	public function AddStep($index,$label,$viewscript,$handler)
    {
    	$this->_wizardData[$index] = array('label'=>$label,'viewscript'=>$viewscript,'handler'=>$handler);
    }
    
	public function AddFinal($handler)
    {
    	$this->_finalHandler = $handler;
    }
    
    public function NextStep($controller)
    {
    	$nextstep   = $this->_currentStep+1;
    	    	
    	$front   	= Zend_Controller_Front::getInstance();
		$request 	= $front->getRequest();
		
    	
    	$params 	= $request->getParams();
    	$redirector = new Zend_Controller_Action_Helper_Redirector();
    		
    	$action 		= $params['action'];
    	$module 		= $params['module'];
    	$controller 	= $params['controller'];
    	$params['s']	= $nextstep;
    	
    	unset($params['action']);
    	unset($params['module']);
    	unset($params['controller']);
    		
    	$redirector->gotoSimple($action,$controller,$module,$params);
    	
    	
    }
    
	private function _getNavigation($currentStep,$ulclass="wizard"){
    	
    	$html = '<ul class="'.$ulclass.'">';
    	foreach($this->_wizardData as $idx=>$step){
    		
    		if ($currentStep == $idx)
    			$class = ' class="active"';
    		else
    			$class = "";
    		
    		$html .= '<li'.$class.'>'.$step['label'].'</li>';
    	}
    	$html .= "</ul>";
    	
    	return $html;    	
    }
    
	private function _getOptionalParam($name,$value)
	{
		$front   = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$value   = $request->getParam($name,$value);
		
		return $value;
	}
	
	public function GetViewScript()
	{
		$step 	 = $this->_getOptionalParam('s',1);
		
		return $this->_wizardData[$step]['viewscript'];
	}
	
	private function _maxStep(){
		return count($this->_wizardData);
	}
	
	public function getCurrentStep()
	{
		return $this->_currentStep;
	}
	
	private function _postRedirectGet()
	{
		$front   = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		
    	if ($request->getMethod() == 'POST')
    	{
    		$params 	= $request->getParams();
    		$redirector = new Zend_Controller_Action_Helper_Redirector();
    		
    		$action 	= $params['action'];
    		$module 	= $params['module'];
    		$controller = $params['controller'];
    		
    		unset($params['action']);
    		unset($params['module']);
    		unset($params['controller']);
    		
    		$redirector->gotoSimple($action,$controller,$module,$params);
    	}
    	else
    		echo "get request";
	}
	
	
    
	public function Handle($controller,$baseUrl)
	{
    	$step 	 = $this->_getOptionalParam('s',NULL);
    	
    	if ($step === NULL)							// indien de wizard voor het eerst werd aangeroepen	
    	{
    		$step = 1;						
    		$this->_session->unsetAll();			// clear all saved data
    		
    		if ($this->_initalHandler !== NULL){	// call initial step if defined
    			$handler = $this->_initalHandler;  
    			$controller->$handler($this);
    		}
    	}
    	
    	$this->_currentStep		= $step;
    	
    	
    	
    	$nextstep		= $step+1;
    	$prevStep		= $step-1;
    	
    	if ($step <= $this->_maxStep())
    		$handler = $this->_wizardData[$step]['handler'];
    	else
    		$handler = $this->_finalHandler;   	
    		
    	$controller->view->wiznav 	= $this->_getNavigation($step,"wizard");
	    $controller->view->urlnext 	= $baseUrl."/s/".$nextstep;
	    $controller->view->urlprev  = ($prevStep > 0) ? $baseUrl."/s/".$prevStep : $this->_cancelUrl;
	    $controller->view->urlcurr  = $baseUrl."/s/".$step;
    	    	
    	return $controller->$handler($this);    		
    }
}