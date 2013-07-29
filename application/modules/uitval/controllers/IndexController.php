<?php

require_once "BaseController.php";

class Uitval_IndexController extends Uitval_BaseController
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
		$date 			  		= $this->_getOptionalParam('datum',date("d-m-Y"));
    	$ret  			  		= $this->_handleForm($this->_getForm('filter'),array('date'=>$date),"_handleFilterForm",NULL);
		
		$this->view->date       = $date;
	}
	
	public function categoryAction()
    {
    	$date 			  		= $this->_getRequiredParam('datum');
    	    	
    	$this->view->date 		= $date;
    	$range			  		= $this->getDateRange($date,NULL);
    	$category				= array();		
    	$lijnnr					= array();		
    	$regs					= array();
    	$totaal_per_category    = array();
    	$totaal_per_lijn        = array();
    	
    	// fetch per categorie, per lijn
    	
    	$data 					= App_ModelFactory::getModel("uitval")->fetchByCategoryAndLijnnr($range,$this->_getCurrentSiteId());
    	//$this->p($data,1);
    	
    	if (!empty($data))
    	{
	    	foreach($data as $rec)
	    		$category[$rec['categorie']] = true;								// bool array van categoieen
	    	foreach($data as $rec)
	    		$lijnnr[$rec['lijnnr']]		 = true;								// bool array van lijnnr's
	    	foreach($data as $rec)
	    		$regs[$rec['lijnnr']][$rec['categorie']] = $rec;					// 2d array per lijn, per categorie: het gewicht
	    		
    		foreach($data as $rec){
	    		if (isset($totaal_per_category[$rec['categorie']]))					// totaal gewicht per categorie
	    			$totaal_per_category[$rec['categorie']] += $rec['gewicht'];
	    		else
	    			$totaal_per_category[$rec['categorie']] = $rec['gewicht'];
	    	
	    		if (isset($totaal_per_lijn[$rec['lijnnr']]))						// totaal gewicht per lijn
	    			$totaal_per_lijn[$rec['lijnnr']] += $rec['gewicht'];
	    		else
	    			$totaal_per_lijn[$rec['lijnnr']] = $rec['gewicht'];
	    	}
    	}
    	
    	$this->view->data				 = $regs;					// alle data
    	$this->view->category			 = $category;				// boolean array van voorkomende categorieen
    	$this->view->lijnnr				 = $lijnnr;					// boolean array van lijnnr's
    	$this->view->totaal_per_category = $totaal_per_category;	// array met totaal gewicht per categorie
    	$this->view->totaal_per_lijn	 = $totaal_per_lijn;		// array met totaal gewicht per lijn
    	
    	//$this->p($regs,1);
    }
    
    public function bakAction()
    {
    	$date 			  		= $this->_getRequiredParam('datum');
    	    	
    	$this->view->date 		= $date;
    	$range			  		= $this->getDateRange($date,NULL);
    	
    	$bakken                 = Zend_Registry::getInstance()->uitval_consts->bak->get($this->_getCurrentSiteName())->toArray();
    	foreach($bakken as $idx=>$bak)
    		$groepen[$bak['groep']][] = $idx;
    		
    	// fetch per groep bak
    	foreach($groepen as $groepnaam=>$groep){
    		$data[$groepnaam] = App_ModelFactory::getModel("uitval")->fetchByBak($range,$this->_getCurrentSiteId(),$groep);    		
    	}
    	
    	//$this->p($data,0);
    	
    	$this->view->data = $data;
    	
    	//$this->p($totaal_per_bak);
    }
    
    private function _Dienstnaam($diensten,$dienst_id)
    {
    	$sh = $diensten['start'][$dienst_id]['uur'];
    	$sm = $diensten['start'][$dienst_id]['minuten'];
    	
    	$eh = $diensten['einde'][$dienst_id]['uur'];
    	$em = $diensten['einde'][$dienst_id]['minuten'];
    	
    	if ($sh < 0)
    		$sh += 24;
    	if ($sh > 24)
    		$sh -= 24;
    	if ($eh < 0)
    		$eh += 24;
    	if ($eh > 24)
    		$eh -= 24;
    		
    	$sh = str_pad($sh, 2, "0", STR_PAD_LEFT);
    	$eh = str_pad($eh, 2, "0", STR_PAD_LEFT);
    	$sm = str_pad($sm, 2, "0", STR_PAD_LEFT);
    	$em = str_pad($em, 2, "0", STR_PAD_LEFT);
    		
    	return "Dienst $dienst_id ($sh:$sm - $eh:$em)";
    }
    
    public function dienstAction()
    {
    	$date 			  		= $this->_getRequiredParam('datum');
    	$this->view->date 		= $date;
    	$lijnen 				= array();
    	$diensten               = Zend_Registry::getInstance()->sites->dienst->get($this->_getCurrentSiteName())->toArray();
    	$totaal_per_dienst      = array();
    	
    	//$this->p($diensten,0);
    	
    	foreach($diensten['start'] as $dienst_id=>$start){
    		
    		$totaal_per_dienst[$dienst_id] = 0;
    		$gewichten_per_lijn			   = array();
    		$shift[$dienst_id]  		   = $this->_Dienstnaam($diensten,$dienst_id);
    		
    		$range = $this->getDateRange($date);
    		//$this->p($range,1);
    		
    		$regs  = App_ModelFactory::getModel("uitval")->fetchByLijnnr($range,$this->_getCurrentSiteId(),$dienst_id);
    		
    		
    		
    		if (!empty($regs)){
    			$totaal = 0;
	    		foreach($regs as $reg){	    			
	    			$gewichten_per_lijn[$reg['lijnnr']]  = $reg['gewicht'];
	    			
	    			if (isset($lijnen[$reg['lijnnr']]))
	    				$lijnen[$reg['lijnnr']]		 	+= $reg['gewicht'];
	    			else
	    				$lijnen[$reg['lijnnr']]			 = $reg['gewicht'];
	    				
	    			$totaal							    += $reg['gewicht'];
	    		}
	    		$totaal_per_dienst[$dienst_id] = $totaal;
	    	}
    		
    		//$this->p($lijn,1);
    		$data[$dienst_id] = $gewichten_per_lijn;
    		
    	}
    	
    	ksort($lijnen);
    	
    	$this->view->data 				= $data;
    	$this->view->diensten 			= $shift;
    	$this->view->lijnen 			= $lijnen;
    	$this->view->totaal_per_dienst 	= $totaal_per_dienst;
    	
    }
    
	
    
    public function formatActions($entry)
    {
    	$output       = "";
    	
    	$output .= $this->renderActionIcon('Bewerken','ui-icon-pencil',array('id'=>$entry['id']),'edit');
    	$output .= $this->renderActionIcon('Verwijderen','ui-icon-trash',array('id'=>$entry['id']),'delete');
    	
    	
    	return $output;	
    }
    
	public function tableGetEntries($helper,$order,$sort)
    {
    	$date 	= $helper->getFilterValue("date");
    	$range	= $this->getDateRange($date,NULL);
    	
    	$entries = App_ModelFactory::getModel("uitval")->fetchByDate($range,$this->_getCurrentSiteId(),NULL,$order,$sort);
    	
    	return $entries;
    }
    
	public function dumpGetEntries($helper,$order,$sort)
    {
    	$datefrom 	= $helper->getFilterValue("datefrom");
    	$dateto 	= $helper->getFilterValue("dateto");
    	$rangefrom	= $this->getDateRange($datefrom,NULL);
    	$rangeto	= $this->getDateRange($dateto,NULL);
    	$range      = array('min'=>$rangefrom['min'],'max'=>$rangeto['max']);
    	
    	$entries = App_ModelFactory::getModel("uitval")->fetchByDate($range,$this->_getCurrentSiteId(),NULL,$order,$sort);
    	
    	return $entries;
    }
    
    public function tableAction()
    {
    	$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("date","Datum: ",date("d-m-Y"));
    	$helper->setGetEntries("tableGetEntries");
    	$helper->setConfig(Zend_Registry::getInstance()->uitval_tables->index->table);
    	
    	
    	$helper->postRedirectGet();    	
    }
    
	public function dumpAction()
    {
    	$helper = new App_TableHelper($this,$this->getRequest());
    	$helper->addDateFilter("datefrom","Datum vanaf: ",date("d-m-Y"));
    	$helper->addDateFilter("dateto","t/m: ",date("d-m-Y"));
    	$helper->setGetEntries("dumpGetEntries");
    	$helper->setConfig(Zend_Registry::getInstance()->uitval_tables->index->tablecsv);
    	
    	
    	$helper->postRedirectGet();    	
    }
    
    private function _getEditData($id)
    {
    	$data	= App_ModelFactory::getModel("uitval")->fetchEntry($id);
    	
    	$data['gewicht_bruto'] = number_format($data['gewicht_bruto']/10,1,",","");
    	$data['gewicht_netto'] = number_format($data['gewicht_netto']/10,1,",","");
    	$data['categorie'] 	   = $data['categorie']."#".$data['lijnnr'];
    	
    	return $data;
    }
    
    public function handleEdit($form)
    {
    	$data 				= $form->getValues();
    	$id  				= $this->_getRequiredParam('id');
    	
    	$controller         = $this->_getRequiredParam('controller');
    	$module		        = $this->_getRequiredParam('module');
    	
    	unset($data['frmid']);
    	
    	$data['tijd'] 		= $this->date2Iso($data['tijddatum'])." ".$data['tijdtijd'].":00";    	
    	
    	unset($data['tijddatum']);
    	unset($data['tijdtijd']);
    	
    	
    	$cats 				= explode("#",$data['categorie']);
    	$data['categorie'] 	= $cats[0];
    	
    	$data				= $this->_setGewicht($data,$this->_getCurrentSiteId());
    	
    	App_ModelFactory::getModel("uitval")->update($id,$data);
    	
    	$this->_redirector->gotoSimple('table',$controller,$module,array());		
    	
    }

	public function editAction()
    {
    	$id  	= $this->_getRequiredParam('id');
    	$form   = $this->_initRegistratieForm($this->_getForm('edit'));
    	$data   = $this->_getEditData($id);
    	
    	$data['tijddatum'] 	= substr($data['tijd'],0,10);
    	$data['tijdtijd'] 	= substr($data['tijd'],12,5);
    	
    	//$this->p($data);
    	
    	$ret 	= $this->_handleForm($form,$data,"handleEdit");
    }
    
	public function deleteAction()
    {
    	$confirm  	= $this->_getOptionalParam('confirm',false);
    	$id  		= $this->_getRequiredParam('id');
    	
    	if ($confirm){
    		App_ModelFactory::getModel("uitval")->delete($id);
    		$this->_redirector->gotoSimple('table',$controller,$module,array());    	
    	}
    }
    
}