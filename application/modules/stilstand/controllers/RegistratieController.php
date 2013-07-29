<?php

require_once "BaseController.php";

class Stilstand_RegistratieController extends Stilstand_BaseController
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
    	
    	if ($formname == 'registratie'){
    		
    		$form = $this->_initRegistratieForm($form);
    	}
    	else if($formname == 'confirm'){    		
    		
    		$configFileName = Zend_Registry::getInstance()->stilstand_forms->registratie;    	
    		$cfg = new Zend_Config_Ini(APPLICATION_PATH."/".$configFileName, APPLICATION_ENV);    	
    		$form = $this->removeHiddenFields($form,$cfg->hide->confirm,$this->_getCurrentSiteName());    		
    	}
    	 	
    	return $form;    	
    }
	
    protected function _handleRegistration($form)
    {
    	$data 				= $form->getValues();
    	$controller         = $this->_getRequiredParam('controller');
    	$module		        = $this->_getRequiredParam('module');
    	
    	unset($data['frmid']);
    	$cats 					= explode("#",$data['categorie']);
    	$data['categorie'] 		= $cats[0];
		
		$subcats				= explode("#",$data['subcategorie']);
    	$data['subcategorie'] 	= $subcats[0];
    	
    	$this->_redirector->gotoSimple('confirm',$controller,$module,$data);		
    }
    
    private function _computeNumMinutes($start,$end,$nextday)
    {
    	$sp = explode(":",$start);
    	$ep = explode(":",$end);
    	
    	$today 		= date("Y-m-d");
    	$tomorrow	= date("Y-m-d",strtotime("+1 day"));
    	
    	$start = $today." ".$start.":00";
    	if ($nextday)
    		$end = $tomorrow." ".$end.":00";
    	else
    		$end = $today." ".$end.":00";
    		
    	$diff = (strtotime($end)-strtotime($start))/60;
    	
    	return $diff;
    }
    
    protected function _handleConfirmation($form)
    {
    	$data 					= $form->getValues();
    	$controller         	= $this->_getRequiredParam('controller');
    	$module		        	= $this->_getRequiredParam('module');
    	$cancel					= $this->_getOptionalParam('cancel',false);
    	
    	
    	$rec                	= array();
    	$rec['starttijd']		= $this->_getRequiredParam('starttijd');
    	$rec['eindtijd']		= $this->_getRequiredParam('eindtijd');
    	$rec['produktieorder']	= $this->_getOptionalParam('produktieorder',NULL);
    	
    	if (substr($rec['eindtijd'],0,1) == '>')
    	{
    		$end     = substr($rec['eindtijd'],1);
    		$nextday = true;
    	}
    	else
    	{
    		$end     = $rec['eindtijd'];
    		$nextday = false;
    	}

    	$rec['minuten']			= $this->_computeNumMinutes($rec['starttijd'],$end,$nextday);
    	unset($rec['eindtijd']);
    	
    	$rec['lijnnr']			= $this->_getRequiredParam('lijnnr');
    	$rec['categorie']		= $this->_getRequiredParam('categorie');
		$rec['subcategorie']	= $this->_getRequiredParam('subcategorie');
    	$rec['dienst_id']		= $this->_getRequiredParam('dienst_id');
    	$rec['opmerkingen']		= $this->_getOptionalParam('opmerkingen',"");
    	$rec['site_id'] 		= $this->_getCurrentSiteId();
    	
    	//$this->p($rec,1);
    	
    	
   		if (!$cancel){
    		App_ModelFactory::getModel('stilstand')->save($rec);    		
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
    	$lijn = $this->_getRequiredParam('lijnnr');
    	$date = date("d-m-Y");
    	$data = NULL;
    	
    	$config		= Zend_Registry::getInstance()->eniac->resources->get($this->_getCurrentSiteName());
    	if ($config !== NULL){
		
    		// prefill huidige produktieorder
    		$eniaclijnnr = Zend_Registry::getInstance()->sites->lijn->get($this->_getCurrentSiteName())->get($lijn)->eniacid;
    		$order = App_ModelFactory::getModel("Planning")->fetchCurrentOrder($eniaclijnnr,$date,$this->_getCurrentSiteName());
    		if (isset($order['VTORNR']))
    			$data['produktieorder'] = $order['VTORNR'].$order['VTORRL'];
    	}
    	
		$ret  = $this->_handleForm($form,$data,"_handleConfirmation");	
    }
}