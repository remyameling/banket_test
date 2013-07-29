<?php

require_once "BaseController.php";

class Uitval_RegistratieController extends Uitval_BaseController
{
	protected $_model 			= NULL;
	protected $_flashmessenger  = NULL;
	protected $_domain_name 	= "registratie";
	
	public function init()
    {
    	$this->_flashmessenger	= $this->_helper->getHelper('FlashMessenger');
     
    	$this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
						  
        return parent::init();
    }
    
    
    
    protected function _getForm($formname)
    {
    	$form = parent::_getForm($formname);
    	
    	if ($formname == 'registratie')
    		$form = $this->_initRegistratieForm($form);
    	 	
    	return $form;    	
    }
	
    protected function _handleRegistration($form)
    {
    	$data 				= $form->getValues();
    	$controller         = $this->_getRequiredParam('controller');
    	$module		        = $this->_getRequiredParam('module');
    	
    	unset($data['frmid']);
    	$cats 				= explode("#",$data['categorie']);
    	$data['categorie'] 	= $cats[0];
    	
    	$this->_redirector->gotoSimple('confirm',$controller,$module,$data);		
    }
    
    
    
	protected function _handleConfirmation($form)
    {
    	$data 				= $form->getValues();
    	$controller         = $this->_getRequiredParam('controller');
    	$module		        = $this->_getRequiredParam('module');
    	$cancel				= $this->_getOptionalParam('cancel',false);
    	
    	$rec                		= array();
    	$rec['baktype']				= $this->_getRequiredParam('baktype');
    	$rec['gewicht_bruto']		= $this->_getRequiredParam('gewicht');
    	$rec['lijnnr']				= $this->_getRequiredParam('lijnnr');
    	$rec['categorie']			= $this->_getRequiredParam('categorie');
    	$rec['dienst_id']			= $this->_getRequiredParam('dienst_id');
    	$rec['opmerkingen']			= $this->_getOptionalParam('opmerkingen',"");
    	$rec['produktieorder']		= $this->_getOptionalParam('produktieorder',NULL);
    	$rec['site_id'] 			= $this->_getCurrentSiteId();
    	
    	
   		if (!$cancel)
   		{
    		$rec = $this->_setGewicht($rec,$this->_getCurrentSiteId());
   			
   			App_ModelFactory::getModel('uitval')->save($rec);
    		
    		$this->_flashmessenger->addMessage('Registratie opgeslagen !');	    	
	    }
    		
    	$this->_redirector->gotoSimple('index',$controller,$module,array());			
    }
    
    public function indexAction()
    {
    	$form 				   = $this->_getForm('registratie');
    	$messages              = $this->_helper->getHelper('FlashMessenger')->getMessages();

    	if (count($messages) > 0)
    		$this->view->message = $messages[0];
    		
    	$ret  = $this->_handleForm($form,NULL,"_handleRegistration");
    }
    
    public function confirmAction()
    {
    	$form = $this->_getForm('confirm');
		
		$ret  = $this->_handleForm($form,NULL,"_handleConfirmation");	
    }
}