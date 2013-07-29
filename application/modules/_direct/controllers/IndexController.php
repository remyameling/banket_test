<?php

require_once "BaseController.php";

class Direct_IndexController extends Direct_BaseController
{
	protected $_model 			= NULL;
	protected $_domain_name 	= "index";

	const CLASSNAME_PARTLY				= 'partly';
	const CLASSNAME_NOT_REGISTERED		= 'not';
	
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
	
	private function _getOrderNummers($orders){
		$ordernrs = array();
		
		//$this->p($orders,1);
		
		foreach($orders as $lijnorder){
			
			
			
			foreach($lijnorder as $order)
				$ordernrs[$order['PDORNR'].$order['PDORRL']] = array('NUM_DOZEN'=>$order['NUM_DOZEN'],
																	 'NUM_DOZEN_AFGEMELD'=>0,				// initialiseer NUM_DOZEN_AFGEMELD op 0
																	 'NUM_PALLETS_GEREEDGEMELD'=>$order['NUM_PALLETS_GEREEDGEMELD'],
																	 'NUM_DOZEN_PER_PALLET'=>$order['NUM_DOZEN_PER_PALLET'],
																	 'MAX_TIME'=>$order['MAX_TIME']); 		// 
		}
		return $ordernrs;
	}
	
	private function _substractRegisteredOrders($orders)
	{
		$ordernrs = array();
		foreach($orders as $ordernr=>$data)
			$ordernrs[$ordernr] = $ordernr;
			
		$registered_orders = App_ModelFactory::getModel('direct')->fetchWhereOrdernrIn($ordernrs,$this->_getCurrentSiteId());
		
		foreach($registered_orders as $order){
			
			//$this->p($order,1);
			
			$num_dozen 		= $orders[$order['ordernr']]['NUM_DOZEN'];				// aantal geplande dozen in Eniac
			$num_afgemeld	= $order['aantaldozen'];								// aantal afgemelde dozen in onze database
			
			if ($num_afgemeld >= $num_dozen)										// indien aantal afgemeld > aantal gepland
				unset($orders[$order['ordernr']]);									// dan verwijder deze order
			else 
				$orders[$order['ordernr']]['NUM_DOZEN_AFGEMELD'] = $num_afgemeld;	// anders: bewaar aantal afgemeld
		}
		
		return $orders;
	}
	
	private function _getAfmeldUrl($orders)
	{
		$planned_ordernrs 	= $this->_getOrderNummers($orders);							// haal op: alle geplande orders
		$active_ordernrs 	= $this->_substractRegisteredOrders($planned_ordernrs);		// verwijder alle afgemelde orders
		$module		        = $this->_getRequiredParam('module');
		
		//$this->p($orders,0);
		
		$url   = array();
		$class = array();
		$info  = array();
		
		foreach($active_ordernrs as $ordernr=>$data)
		{
			//$this->p($data);
			
			$ornr = substr($ordernr,0,8);   //strlen($ordernr)-3);
			$orrl = substr($ordernr,8,strlen($ordernr)-8);
			
			$url[$ordernr] = $this->view->url(array('module'=>$module,'controller'=>'registratie','action'=>'index','ordernr'=>$ornr,'orderrl'=>$orrl),NULL,true);
			if ($data['NUM_DOZEN_AFGEMELD'] > 0)
				$class[$ordernr] = self::CLASSNAME_PARTLY;
			else
				$class[$ordernr] = self::CLASSNAME_NOT_REGISTERED;
				
			$info[$ordernr]['planned']   	 =  (int) $data['NUM_DOZEN'];
			$info[$ordernr]['gereedgemeld']  = ((int) $data['NUM_PALLETS_GEREEDGEMELD'])*((int) $data['NUM_DOZEN_PER_PALLET']);
			$info[$ordernr]['afgemeld']  	 =  (int) $data['NUM_DOZEN_AFGEMELD'];
		}
		
		return array('url'=>$url,'class'=>$class,'info'=>$info);
	}
	
	public function indexAction()
	{
		$date		= $this->_getOptionalParam('datum',date("d-m-Y"));
		$lijn		= $this->_getOptionalParam('lijn',1);
    	$ret  		= $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
    	$messages   = $this->_helper->getHelper('FlashMessenger')->getMessages();
    	
    	$mdl 		= App_ModelFactory::getModel("Planning");
    	$lijnen     = Zend_Registry::getInstance()->sites->lijn->get($this->_getCurrentSiteName())->toArray();
    	
    	foreach($lijnen as $index=>$lijn)
    		$orders[$index] = $mdl->fetchPlannedOrders($lijn['eniacid'],$date,$this->_getCurrentSiteName());
    		
    	
    		
    	$urls                       = $this->_getAfmeldUrl($orders);
    		
    	//$this->p($urls['info'],1);
    	
    	$this->view->register_urls	= $urls['url'];
    	$this->view->classes		= $urls['class'];
    	$this->view->info			= $urls['info'];
    	$this->view->date         	= $date;
    	$this->view->orders       	= $orders;
    	$this->view->lijnen       	= $lijnen;
    	
    	if (count($messages) > 0)
    		$this->view->message = $messages[0];
    	
	}
	
	public function formatActions($entry)
    {
    	$date				= $this->_getOptionalParam('date',NULL);
    	
    	$output       = "";
    	
    	$output .= $this->renderActionIcon('Bewerken','ui-icon-pencil',array('id'=>$entry['id'],'date'=>$date),'edit');
    	$output .= $this->renderActionIcon('Verwijderen','ui-icon-trash',array('id'=>$entry['id']),'delete');
    	
    	
    	return $output;	
    }
	
	public function tabGetEntries($helper,$order,$sort)
    {
    	$date 	= $helper->getFilterValue("date");
    	$range	= $this->getDateRange($date,NULL);
    	
    	$entries = App_ModelFactory::getModel("direct")->fetchByDate($range,$this->_getCurrentSiteId(),NULL,$order,$sort);
    	
    	//$this->p($entries);
    	
    	return $entries;
    }
	
	public function tableAction()
    {
    	$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->index->table);

    	$helper->postRedirectGet();
    }
    
	private function _getEditData($id)
    {
    	$data	= App_ModelFactory::getModel("direct")->fetchEntry($id);
    	
    	$tijddatum  = explode(' ',$data['starttijd']);
    	$starttijd  = explode(':',substr($tijddatum[1],1,8));
    	$tijddatum  = explode(' ',$data['eindtijd']);
    	$eindtijd   = explode(':',substr($tijddatum[1],1,8));
    	
    	$data['starttijd'] = date("H:i",mktime($starttijd[0],$starttijd[1]));
    	$data['eindtijd']  = date("H:i",mktime($eindtijd[0],$eindtijd[1],0));
    	$data['aantalfte'] = number_format($data['aantalfte']/10,1,",","");
    	
    	return $data;
    }
    
	protected function handleEdit($form)
    {
    	$data 				= $form->getValues();
    	$id  				= $this->_getRequiredParam('id');
    	$controller         = $this->_getRequiredParam('controller');
    	$module		        = $this->_getRequiredParam('module');
    	$date				= $this->_getOptionalParam('date',date("d-m-Y"));
    	
    	unset($data['frmid']);
    	
    	// ophalen datum van oorspronkelijke registratie
    	
    	$datedat = App_ModelFactory::getModel("direct")->fetchEntry($id);
    	$ordernr = $datedat['ordernr'];
    	
    	// update other fields
    	    	
    	$data['site_id'] 	= $this->_getCurrentSiteId();    	
    	
    	$prod_datum			= $this->_OrdernrToDate($ordernr);    	
    	$data['starttijd']  = $this->_toDateTime($data['starttijd'],$prod_datum,$prod_datum);
    	$data['eindtijd']   = $this->_toDateTime($data['eindtijd'],$prod_datum,substr($data['starttijd'],0,10));
    	$data['aantalfte']  = $this->_setFTE($data['aantalfte']);
    	
    	App_ModelFactory::getModel('direct')->update($id,$data);
    	
    	$this->_redirector->gotoSimple('table',$controller,$module,array('date'=>$date));			
    }
    
	public function editAction()
    {
    	$id  	= $this->_getRequiredParam('id');
    	$form   = $this->_initRegistratieForm($this->_getForm('edit'));
    	$data   = $this->_getEditData($id);
    	
    	$ret 	= $this->_handleForm($form,$data,"handleEdit");
    }
    
	public function deleteAction()
    {
    	$confirm  		= $this->_getOptionalParam('confirm',false);
    	$id  			= $this->_getRequiredParam('id');
    	
    	if ($confirm){
    		$Mdl = App_ModelFactory::getModel("direct");
    		$Mdl->delete($id);
    		
    		$this->_redirector->gotoSimple('table');    	
    	}
    }
    
	public function dumpGetEntries($helper,$order,$sort)
    {
    	$datefrom 	= $helper->getFilterValue("datefrom");
    	$dateto 	= $helper->getFilterValue("dateto");
    	$rangefrom	= $this->getDateRange($datefrom,NULL);
    	$rangeto	= $this->getDateRange($dateto,NULL);
    	$range      = array('min'=>$rangefrom['min'],'max'=>$rangeto['max']);
    	
    	$entries = App_ModelFactory::getModel("direct")->fetchByDate($range,$this->_getCurrentSiteId(),NULL,$order,$sort);
    	
    	return $entries;
    }
    
	public function dumpAction()
    {
    	$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("datefrom","Datum vanaf: ",date("d-m-Y"));
    	$helper->addDateFilter("dateto","t/m: ",date("d-m-Y"));
    	$helper->setGetEntries("dumpGetEntries");
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->index->tablecsv);
    	
    	$helper->postRedirectGet();    	
    }
	
	    
	   
}