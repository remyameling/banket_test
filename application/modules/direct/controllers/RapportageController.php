<?php

require_once "BaseController.php";

class Direct_RapportageController extends Direct_BaseController
{
	protected $_model 			= NULL;
	protected $_domain_name 	= "magazijnpallets";
	
	public function init()
    {
    	$this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
						  
		$this->view->site_name 	= $this->_getCurrentSiteName();
		
		return parent::init();
	}
	
	protected function _handleFilterForm($form)
	{
		$module 	= $this->_getRequiredParam('module');
		$controller = $this->_getRequiredParam('controller');
		$action 	= $this->_getRequiredParam('action');
		
		$data = $form->getValues();
		$date = $data['date'];
		
		$this->_redirector->gotoSimple($action,$controller,$module,array('date'=>$date));
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
	
	
	public function magazijnpalletsGetEntries($helper,$order,$sort)
    {
    	$date 	= $this->_getOptionalParam('date',date("d-m-Y"));
    	$range	= $this->getDateRange($date,NULL);
    	
    	$entries = App_ModelFactory::getModel("Pallets")->fetchMagazijnpallets($date,$this->_getCurrentSiteName(),$order,$sort);
    	
    	return $entries;
    }
    
	public function magazijnpalletsGetActions($entry)
    {
    	$date				= $this->_getOptionalParam('date',NULL);
    	
    	$output  = "";    	
    	$output .= $this->renderActionIcon('Bekijk pallets','ui-icon-zoomin',array('artikelnr'=>trim($entry['VTALNR']),'ordernr'=>$entry['VTORNR'],'magazijncode'=>$entry['QLMAGN']),'view');
    	
    	return $output;	
    }
    
    public function magazijnpalletsAction()
	{
		$date = $this->_getOptionalParam('date',date("d-m-Y"));
    	$ret  = $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
    	
    	
		
		$this->view->date       = $date;
		
		$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->rapportage->magazijnpallets);

    	$helper->postRedirectGet();
	}
	
	public function palletsGetEntries($helper,$order,$sort)
    {
    	$date 	= $this->_getOptionalParam('date',date("d-m-Y"));
		$range	= $this->getDateRange($date,NULL);
		
		$entries = App_ModelFactory::getModel("Pallets")->fetchAll($date,$this->_getCurrentSiteName(),$order,$sort);
		//$this->p($entries);
    	
    	return $entries;
    }
	
	public function palletsAction()
	{
		$date = $this->_getOptionalParam('date',date("d-m-Y"));
    	$ret  = $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
    	
    	$this->view->date       = $date;
		
		$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->rapportage->pallets);

    	$helper->postRedirectGet();
	}
	
	public function artikelsGetEntries($helper,$order,$sort)
    {
    	$date 	= $this->_getOptionalParam('date',date("d-m-Y"));
		$range	= $this->getDateRange($date,NULL);
		
		$entries = App_ModelFactory::getModel("Pallets")->fetchGroupedByArtikel($date,$this->_getCurrentSiteName(),$order,$sort);
		//$this->p($entries);
    	
    	return $entries;
    }
	
	public function artikelviewLink($entry){
	
		$date 	= $this->_getOptionalParam('date',date("d-m-Y"));
		$vtalnr	= trim($entry['VTALNR']);
			
		return $this->renderActionIcon('Bewerken','ui-icon-pencil',array('vtalnr'=>$vtalnr,'date'=>$date),'artikeldetails');
	}
	
	public function artikelsAction()
	{
		$date = $this->_getOptionalParam('date',date("d-m-Y"));
    	$ret  = $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
    	
    	$this->view->date       = $date;
		
		$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->rapportage->artikels);

    	$helper->postRedirectGet();
	}
	
	public function artikeldetailsGetEntries($helper,$order,$sort)
    {
    	$date 	= $this->_getRequiredParam('date');
    	$vtalnr	= $this->_getRequiredParam('vtalnr');
		
		$entries = App_ModelFactory::getModel("Pallets")->fetchByArtikel($date,$this->_getCurrentSiteName(),$vtalnr,$order,$sort);
		//$this->p($entries);
    	
    	return $entries;
    }
	
	public function artikeldetailsAction()
	{
		$date 	= $this->_getRequiredParam('date');
    	$vtalnr	= $this->_getRequiredParam('vtalnr');
    	
    	$this->view->date       = $date;
		
		$helper = new App_TableHelper($this,$this->getRequest());
    	//$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->rapportage->artikeldetails);

    	$helper->postRedirectGet();
	}
	
	public function viewGetEntries($helper,$order,$sort)
    {
    	$ordernr 		= $this->_getRequiredParam('ordernr');
		$magazijncode	= $this->_getRequiredParam('magazijncode');
		$artikelnr		= $this->_getRequiredParam('artikelnr');
    	
    	$entries = App_ModelFactory::getModel("Pallets")->fetchByOrderAndCode($this->_getCurrentSiteName(),$artikelnr,$ordernr,$magazijncode,$order,$sort);
    	
    	return $entries;
    }

	public function viewAction()
	{
		$ordernr 		= $this->_getRequiredParam('ordernr');
		$magazijncode	= $this->_getRequiredParam('magazijncode');
		$artikelnr		= $this->_getRequiredParam('artikelnr');
    	
		$helper = new App_TableHelper($this,$this->getRequest());
		
		$helper->setConfig(Zend_Registry::getInstance()->direct_tables->rapportage->view);

    	$helper->postRedirectGet();
	}
	
	private function setOrdernrAsKey($array){
		
		$temp = array();
		foreach($array as $key=>$rec){
			$rec['NUM_DOZEN'] = (int) $rec['NUM_DOZEN'];
			$ordernr          = $rec['ORDERNR'];
			$temp[$ordernr]	  = $rec;
		}
		return $temp;		
	}
	
	private function combineArrays($gereedgemeld,$afgemeld,$key){
		
		$gereedgemeld = $this->setOrdernrAsKey($gereedgemeld);
		$afgemeld     = $this->setOrdernrAsKey($afgemeld);
		$combined     = array();
		
		//$this->p($gereedgemeld);
		//$this->p($afgemeld);
		
		foreach($gereedgemeld as $ordernr=>$gereedGemeldRec){
			
			$num_gereedgemeld = $gereedGemeldRec['NUM_DOZEN'];
			if (isset($afgemeld[$ordernr]))
				$num_afgemeld = $afgemeld[$ordernr]['NUM_DOZEN'];
			else
				$num_afgemeld = 0;
				
			$verschil = $num_gereedgemeld-$num_afgemeld;
			
			if ($verschil <> 0){
				
				$combrec 			= array('ordernr'=>$ordernr,'num_gereedgemeld'=>$num_gereedgemeld,'num_afgemeld'=>$num_afgemeld,'verschil'=>$verschil,'omschrijving'=>$gereedGemeldRec['OMSCHRIJVING']);
				$keyval  			= $combrec[$key];
				$combined[$keyval]  = $combrec;
			}						
		}
		
		foreach($afgemeld as $ordernr=>$afGemeldRec){
		
			//print_r($afgemeld);
			//die();
			
			if (!isset($combined[$ordernr])){			
				$num_afgemeld = $afGemeldRec['NUM_DOZEN'];
				if (isset($gereedgemeld[$ordernr]))
					$num_gereedgemeld = $gereedgemeld[$ordernr]['NUM_DOZEN'];
				else
					$num_gereedgemeld = 0;
					
				$verschil = $num_gereedgemeld-$num_afgemeld;
				
				if ($verschil <> 0)
				{
					$combrec 			= array('ordernr'=>$ordernr,'num_gereedgemeld'=>$num_gereedgemeld,'num_afgemeld'=>$num_afgemeld,'verschil'=>$verschil,'omschrijving'=>$afGemeldRec['omschrijving']);
					$keyval  			= $combrec[$key];
					$combined[$keyval]  = $combrec;
				}				
			}						
		}
		
		//$this->p($combined);
		
		return $combined;
		
	}
	
	public function afmeldverschilGetEntries($helper,$order,$sort){
		
		$date = $this->_getOptionalParam('date',date("d-m-Y"));
		
		$gereedGemeldeOrders    = App_ModelFactory::getModel("Pallets")->fetchCountByOrdernum($this->_getCurrentSiteName(),$date);
    	$afgemeldeOrders        = App_ModelFactory::getModel("Direct")->fetchCountByOrdernum($this->_getCurrentSiteId(),$date);    	
    	$combined               = $this->combineArrays($gereedGemeldeOrders,$afgemeldeOrders,$order);
    	
    	return $combined;
	}
	
	public function afmeldverschilAction()
	{
		$date = $this->_getOptionalParam('date',date("d-m-Y"));
    	$ret  = $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
    	
    	$this->view->date       = $date;
    	
    	$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setConfig(Zend_Registry::getInstance()->direct_tables->rapportage->afmeldverschil);

    	$helper->postRedirectGet();    	
	}
}