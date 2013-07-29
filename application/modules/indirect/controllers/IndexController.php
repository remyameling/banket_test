<?php

require_once "BaseController.php";

class Indirect_IndexController extends Indirect_BaseController
{
	protected $_model 			= NULL;
	protected $_domain_name 	= "index";

	public function init()
    {
    	$this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
						  
		$this->view->site_name 	= $this->_getCurrentSiteName();
		
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		
		if (!$contextSwitch->hasContext('csv'))
			$contextSwitch->addContext('csv',array('suffix'=>'csv','headers'=>array('Content-Type'=>'application/csv','Content-Disposition'=>'attachment; filename="output.csv"')));	
				
        $contextSwitch->addActionContext('dump', 'csv')
                      ->initContext();
						  
		return parent::init();
	}
	
	protected function _handleFilterForm($form)
	{
		$module 	= $this->_getRequiredParam('module');
		$controller = $this->_getRequiredParam('controller');
		$action 	= $this->_getRequiredParam('action');
		
		$data = $form->getValues();
		$date = $data['date'];
		
		$this->_redirector->gotoSimple($action,$controller,$module,array('datum'=>$date));
	}
	
	public function indexAction()
	{
		$date		= $this->_getOptionalParam('datum',date("d-m-Y"));
		$ret  		= $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
    	$messages   = $this->_helper->getHelper('FlashMessenger')->getMessages();
    	
    	$mdl 		= App_ModelFactory::getModel("Indirect");
    	$diensten   = Zend_Registry::getInstance()->sites->dienst->get($this->_getCurrentSiteName())->naam->toArray();
    	
    	foreach($diensten as $dienst_id=>$dienst){    		
    		$records          = $mdl->fetchByDateAndDienst($date,$dienst_id,$this->_getCurrentSiteId());
    		$dat              = array();
    		foreach($records as $rec)
    			$dat[$rec['functie_id']] = $rec;
    		
    		$data[$dienst_id] = $dat;
    	}
    	
    	//$this->p($data);
    	
    	$functies   = Zend_Registry::getInstance()->sites->functie->get($this->_getCurrentSiteName())->toArray();
    	
    	$this->view->date         	= $date;
    	$this->view->data			= $data;
    	$this->view->diensten      	= $diensten;
    	$this->view->functies      	= $functies;
    	
    	if (count($messages) > 0)
    		$this->view->message = $messages[0];    	
	}	

	public function dumpGetEntries($helper,$order,$sort)
    {
    	$datefrom 	= $helper->getFilterValue("datefrom");
    	$dateto 	= $helper->getFilterValue("dateto");
    	$rangefrom	= $this->getDateRange($datefrom,NULL);
    	$rangeto	= $this->getDateRange($dateto,NULL);
    	$range      = array('min'=>$rangefrom['min'],'max'=>$rangeto['max']);
    	
    	$entries = App_ModelFactory::getModel("indirect")->fetchByDate($range,$this->_getCurrentSiteId(),$order,$sort);
    	
    	return $entries;
    }
    
	public function dumpAction()
    {
    	$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("datefrom","Datum vanaf: ",date("d-m-Y"));
    	$helper->addDateFilter("dateto","t/m: ",date("d-m-Y"));
    	$helper->setGetEntries("dumpGetEntries");
    	$helper->setConfig(Zend_Registry::getInstance()->indirect_tables->index->tablecsv);
    	
    	$helper->postRedirectGet();    	
    }
}