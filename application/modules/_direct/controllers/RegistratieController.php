<?php

require_once "BaseController.php";

class Direct_RegistratieController extends Direct_BaseController
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
    
	protected function _getForm($form)
    {
		$form = parent::_getForm($form);
		return $this->_initRegistratieForm($form);
	}
    
	
    private function _getLijnnrByLijnid($lijn_id){
    	
    	
    	$lijnen 	= Zend_Registry::getInstance()->sites->lijn->get($this->_getCurrentSiteName());
    	$lijnnrs 	= array();
    	foreach($lijnen as $lijnid => $lijn){
    		
    		$lijndata = $lijn->toArray();
    		$lijnnrs[$lijndata['eniacid']] = $lijnid;
    	}
    	
    	return $lijnnrs[$lijn_id];
    }
    
    protected function _handleRegistration($form)
    {
    	$data 				= $form->getValues();
    	$controller         = $this->_getRequiredParam('controller');
    	$ordernr			= $this->_getRequiredParam('ordernr');
    	$orderrl			= $this->_getRequiredParam('orderrl');
    	$dienst_id			= $this->_getRequiredParam('dienst_id');
    	$module		        = $this->_getRequiredParam('module');
    	$order_data 		= App_ModelFactory::getModel("Planning")->fetchPlannedOrder($ordernr,$orderrl,$this->_getCurrentSiteName());
    	
    	
    	unset($data['frmid']);
    	
    	$module		        	= $this->_getRequiredParam('module');
    	$cancel					= $this->_getOptionalParam('cancel',false);
    	
    	$data['site_id'] 		= $this->_getCurrentSiteId();    
    		
    	$prod_datum				= $this->_OrdernrToDate($ordernr);    	
    	$data['starttijd']  	= $this->_toDateTime($data['starttijd'],$prod_datum,$prod_datum);
    	$data['eindtijd']   	= $this->_toDateTime($data['eindtijd'],$prod_datum,substr($data['starttijd'],0,10));
    	
    	$data['aantalfte']  	= $this->_setFTE($data['aantalfte']);
    	$data['ordernr']    	= $ordernr.$orderrl;
    	$data['dienst_id']  	= $dienst_id;
    	$data['omschrijving']  	= $order_data['PDOMAL'];
    	$data['artikelnr']  	= $order_data['PDALNR'];
    	$data['lijnnr']  		= $this->_getLijnnrByLijnid(substr($ordernr,strlen($ordernr)-1));
    	
    	App_ModelFactory::getModel('direct')->save($data);
    	
    	$this->_flashmessenger->addMessage('Registratie opgeslagen !');	    	
	    	
    	$this->_redirector->gotoSimple('index','index',$module,array());			
    }
    
    private function getPreviousOrder($ordernr,$orderrl){
    	
    	$lijnnr = substr($ordernr,7,1);
    	$year   = substr($ordernr,1,2);
    	$week   = substr($ordernr,3,2);
    	$day    = substr($ordernr,5,1);    	
    	$format = "20$year-W$week-$day";    	
    	$date   = date("d-m-Y", strtotime($format)); 
    	
    	$orders = App_ModelFactory::getModel("Planning")->fetchPlannedOrders($lijnnr,$date,$this->_getCurrentSiteName());
    	    	
    	if (!empty($orders)){
    		
    		// zoek index van huidige orderregel
    		$cur_index = -1;
    		foreach($orders as $idx=>$order)
    			if ($order['PDORRL'] == $orderrl)
    				 $cur_index = $idx;
    				 
    		// bepaal vorige index
    		$index = $cur_index-1;
    		
    		// bepaal vorige orderregel
    		if ($index >= 0)
    			return $orders[$index];
    		else
    			return NULL; /* niets gevonden */
    	}
    	else
    		return NULL;
    }
    
    private function getShiftStart(){
    	
    	$dienst_id = $this->getCurrentDienstId();
    	$site_name = $this->_getCurrentSiteName();
    	
    	$start     = Zend_Registry::getInstance()->sites->dienst->get($site_name)->start->get($dienst_id)->toArray();
    	
    	if ($start['uur'] < 0)
    		$start['uur'] = 24-$start['uur'];
    	if ($start['uur'] > 24)
    		$start['uur'] = $start['uur']-24;
    		
    	$tijd = str_pad($start['uur'],2,'0',STR_PAD_LEFT).':'.str_pad($start['minuten'],2,'0',STR_PAD_LEFT);
    	
    	//$this->p($tijd);
    	
    	return $tijd;
    }
    
    private function getInitialStartTijd($ordernr,$orderrl){
    	
    	// get Initieele starttijd:
    	//
    	// = eindtijd van vorige deel-afmelding, indien van toepassing
    	// = gereedmelding van vorige order, indien er geen vorige deel-afmelding is
    	// = start van dienst, indien er geen vorige deel-afmelding of vorige order is
    	
    	$time    	= $this->getShiftStart($this->getShiftStart()); // start tijd van shift
    	$directMdl 	= App_ModelFactory::getModel('Direct');
    	
    	$tijd 		= $directMdl->fetchLatestEndtime($ordernr.$orderrl,$this->_getCurrentSiteId());	// ophalen tijd van vorige deel-afmelding
    	
    	if ($tijd['eindtijd'] !== NULL) // indien er deelafmeldingen zijn voor deze order
    	{
    		$time  = substr($tijd['eindtijd'],11,5);
    	}
    	else // geen deelafmeldingen voor deze order, get gereedmelding van vorige order.
    	{
    		$previous_order 		= $this->getPreviousOrder($ordernr,$orderrl);
    		if ($previous_order !== NULL)
    		{
    			$starttime      	= App_ModelFactory::getModel("Planning")->fetchLatestReady($ordernr,$previous_order['PDORRL'],$this->_getCurrentSiteName());
				
				if (isset($starttime['MAX_TIME'])){
					$time = str_pad($starttime['MAX_TIME'], 6, "0", STR_PAD_LEFT);
    				$time = substr($time,0,2).":".substr($time,2,2);    			
				}
    		}	
    	}
    	
    	return $time;
    	
    	$this->p($time,1);
    	
    }
    
    public function indexAction()
    {
    	$form 				= $this->_getForm('registratie');
    	$ordernr			= $this->_getRequiredParam('ordernr');
    	$orderrl			= $this->_getRequiredParam('orderrl');
    	$num_afgemeld		= 0;
    	
    	$order_data 		= App_ModelFactory::getModel("Planning")->fetchPlannedOrder($ordernr,$orderrl,$this->_getCurrentSiteName());
    	$registered_orders  = App_ModelFactory::getModel('direct')->fetchWhereOrdernrIn($ordernr.$orderrl,$this->_getCurrentSiteId());
    	
    	if (!empty($registered_orders))
    		$num_afgemeld = (int) $registered_orders[0]['aantaldozen'];
    	
    	//$this->p($registered_orders,0);
    	
    	if (!empty($order_data))
    	{
			// init view data
			
			$this->view->num_afgemeld 					= (int) $num_afgemeld;								// aantal dozen afgemeld (in dit tool)
    		$this->view->num_gereedgemeld               = (int) $order_data['NUM_PALLETS_GEREEDGEMELD'] * (int) $order_data['NUM_DOZEN_PER_PALLET'];
			$this->view->num_geproduceerd 				= (int) $order_data['NUM_DOZEN'];					// aantal te produceren dozen
    		$this->view->omschrijving	 				= $order_data['PDOMAL'];							// artikel omschrijving
		
    		// initialiseer aantal dozen
			$aantal = round($this->view->num_gereedgemeld-$this->view->num_afgemeld);
			if ($aantal > 0)
				$data['aantaldozen'] = $aantal;
			
			// initialiseer eindtijd
			if (!empty($order_data['MAX_TIME'])){
				$time = str_pad($order_data['MAX_TIME'], 6, "0", STR_PAD_LEFT);
    			$data['eindtijd'] 	 = substr($time,0,2).":".substr($time,2,2);				
			}
    			
    		// initialiseer starttijd => eindtijd van vorige => tijd dat laatste pallet van vorige order is afgemeld
    		// of eindtijd van vorige deelorder, indien van toepaasing
    		
			$data['starttijd'] 	 	= $this->getInitialStartTijd($ordernr,$orderrl);
			
    		
    		
    	}
    	else
    		$data = NULL;	

    	//$this->p($data);
    	
    	$ret  = $this->_handleForm($form,$data,"_handleRegistration");
    }
    
}