<?php

require_once APPLICATION_PATH.Zend_Registry::getInstance()->paths->components."BaseController.php";

class Direct_BaseController extends RACCMS_Component_BaseController
{
	protected $_form 			= NULL;
	protected $_redirector  	= NULL;
	
		
	public function init()
	{
    	$this->_redirector 		= $this->_helper->getHelper('Redirector');	
    	    		
        return parent::init();        
    }
    
	protected function _getForm($form="add")
    {
		if (NULL === $this->_form){
			
			$config = Zend_Registry::getInstance()->direct_forms->get(strtolower($this->_domain_name));
			
			if ($config === NULL)
				throw new Exception("BaseController::_getForm(".$form."): geen formulier entry gevonden voor ".$this->_domain_name." in forms.ini");
			
			assert($config !== NULL);
			
			$this->_form = new RAC_Component_Form($config,$form);
		}
		return $this->_form;
	}
	
	protected function _setFTE($aantalfte)
    {
    	$aantalfte = (string) str_replace(".","@",$aantalfte);
		$aantalfte = (string) str_replace(",",".",$aantalfte);
		$aantalfte = (string) str_replace("@",",",$aantalfte);
		
		// rond af op 1 decimaal en converteer naar integer		
		
		$aantalfte = (float)  round($aantalfte,1);
		$aantalfte = (int) 10*$aantalfte;
		
		return $aantalfte;   	
    }
	
	protected function _initRegistratieForm($form)
    {
    	// bepaal diensten voor deze site    	
    	$diensten 	= Zend_Registry::getInstance()->sites->dienst->get($this->_getCurrentSiteName())->naam->toArray();
    	$select 	= $form->getElement("dienst_id");
    	$select->addMultiOptions($diensten);
    	
    	// bepaal huidige dienst a.d.h.v. huidige tijd
    	$dienst_id 	= $this->getCurrentDienstId();
    	$select->setValue($dienst_id);
    	
    	// verwijder hidden fields
    	$configFileName = Zend_Registry::getInstance()->uitval_forms->registratie;    	
    	$cfg = new Zend_Config_Ini(APPLICATION_PATH."/".$configFileName, APPLICATION_ENV);    	
    	$form = $this->removeHiddenFields($form,$cfg->hide->registratie,$this->_getCurrentSiteName());
    	
    	return $form;
    }
    
    protected function _OrdernrToDateOld($ordernr,$format="Y-m-d"){
    	
    	$year 			= "20".substr($ordernr,1,2);
		$week 			= substr($ordernr,3,2);
		$day  			= substr($ordernr,5,1);		
		$date 			= new DateTime();
		
		$date->setISODate($year,$week,$day);
		return $date->format($format);
    }

    
	protected function _OrdernrToDate($ordernr,$format="Y-m-d"){
		
		if (Zend_Registry::getInstance()->consts->ordernummercodering->methode == "oud")
    		return $this->_OrdernrToDateOld($ordernr,$format);
    	
    	$year  = "201".substr($ordernr,2,1);
    	$month = substr($ordernr,3,2);
    	$day   = substr($ordernr,5,2);
    	
    	$date  = new DateTime($year."-".$month."-".$day);
    	return $date->format($format);
    }  

    private function _maxDate($d1,$d2,$format="Y-m-d"){
    	
    	$mkd1 = mktime(0,0,0,substr($d1,5,2),substr($d1,8,2),substr($d1,0,4));
		$mkd2 = mktime(0,0,0,substr($d2,5,2),substr($d2,8,2),substr($d2,0,4));
		
		if ($mkd1 >= $mkd2)
			return date($format,$mkd1);
		else
			return date($format,$mkd2);
    }
    
	protected function _toDateTime($time,$default_date,$min_date){
		
		$hours 			= substr($time,0,2);
		$sitenaam   	= $this->_getCurrentSiteName();
		$startdienst	= Zend_Registry::getInstance()->sites->dienst->get($sitenaam)->start->get('1')->uur;
		if ($startdienst < 0)
			$startdienst = $startdienst+24;
			
		if ($hours < $startdienst){
			$mkdate 		= mktime(0,0,0,substr($default_date,5,2),substr($default_date,8,2),substr($default_date,0,4)); 
			$default_date 	= date("Y-m-d",strtotime("+1 day",$mkdate));
		}
		
		$date = $this->_maxDate($default_date,$min_date);		
    	$ret  = $date." ".$time.":00";
		
    	return $ret;
    	
    }
}