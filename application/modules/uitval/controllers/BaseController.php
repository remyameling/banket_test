<?php

require_once APPLICATION_PATH.Zend_Registry::getInstance()->paths->components."BaseController.php";

class Uitval_BaseController extends RACCMS_Component_BaseController
{
	protected $_form 			= NULL;
	protected $_redirector  	= NULL;
	
	protected function initLayout($layout_file,$layout_directory)
	{
    	if (!file_exists(realpath($layout_directory)."/".$layout_file.".phtml"))
    		die("RACCMS_Component_BaseController::initLayout(): Layout file bestaat niet: ".realpath($layout_directory)."/$layout_file.phtml");
    	
		$this->_helper->layout->setLayout($layout_file)
							  ->setLayoutPath(realpath($layout_directory));		
    }
	
	protected function _getTemplateBase(){
		
		return (APPLICATION_PATH."/".
    			Zend_Registry::getInstance()->paths->templates_base.
    			WEBSITE."/");
	}
	
	public function init()
	{
    	$this->_redirector 		= $this->_helper->getHelper('Redirector');	
    	    		
        return parent::init();        
    }
    
	protected function _ReturnReferal()
    {
		$referal 		    = $_SERVER['HTTP_REFERER'];
		$this->_redirector  = $this->_helper->getHelper('Redirector');
		
                
        return $this->_redirector->gotoUrl($referal);
    }
    
	protected function _getForm($form="add")
    {
		if (NULL === $this->_form){
			
			$config = Zend_Registry::getInstance()->uitval_forms->get(strtolower($this->_domain_name));
			
			if ($config === NULL)
				throw new Exception("BaseController::_getForm(".$form."): geen formulier entry gevonden voor ".$this->_domain_name." in forms.ini");
			
			assert($config !== NULL);
			
			$this->_form = new RAC_Component_Form($config,$form);
		}
		return $this->_form;
	}
	
	
		
	
	
	protected function _initRegistratieForm($form)
    {
    	// bepaal baktypes voor de huidige site
    	$bakken 	= Zend_Registry::getInstance()->uitval_consts->bak->get($this->_getCurrentSiteName())->toArray();
    	
    	foreach($bakken as $idx=>$bak)
    		$bakvalues[$idx] = $bak['naam'];
    	
    	// tovoegen baktypes aan formulier element
    	$select		= $form->getElement("baktype");
    	$select->addMultiOptions($bakvalues);
    	
    	// bepaal lijnummers van deze site
    	$lijninfo 	= Zend_Registry::getInstance()->sites->lijn->get($this->_getCurrentSiteName())->toArray();
    	foreach($lijninfo as $idx=>$lijndata)
    		$lijnen[$idx] = $lijndata['naam'];
    	
    	// bepaal categorieen voor deze site    		
    	$categorien = Zend_Registry::getInstance()->uitval_consts->cat->get($this->_getCurrentSiteName())->toArray();
    	
    	// maak array van categorieen per lijn 
    	foreach($lijnen as $lijnid=>$lijn)
    	{
    		$cats                = Zend_Registry::getInstance()->uitval_consts->lijn->get($lijnid);
    		
    		if ($cats !== NULL)									// indien deze lijn categorieen heeft gekoppeld
    			$catperlijn[$lijnid] = explode(",",$cats);
    		else												// zoniet, lijn verwijderen uit lijnnummers array
    			unset($lijnen[$lijnid]);
    	}
    	
    	// tovoegen lijnnummers aan formulier element
    	$select = $form->getElement("lijnnr");
    	$select->addMultiOptions($lijnen);
    	
    	// maak filter waardes array (value#filter)
    	foreach($catperlijn as $lijn=>$cats){
    		foreach($cats as $categorie){
    			
    			if (!isset($categorien[$categorie])){
    				$this->p($cats,1);
    				throw new Exception("Categorie $categorie not set");
    			}    			
    			$values["$categorie#$lijn"] = $categorien[$categorie];
    		}
    	}
    	
    	// bepaal diensten voor deze site    	
    	$diensten 	= Zend_Registry::getInstance()->sites->dienst->get($this->_getCurrentSiteName())->naam->toArray();
    	$select 	= $form->getElement("dienst_id");
    	$select->addMultiOptions($diensten);
    	
    	// bepaal huidige dienst a.d.h.v. huidige tijd
    	$dienst_id 	= $this->getCurrentDienstId();
    	$select->setValue($dienst_id);
    	
    	// toevoegen filter waardes aan formulier element    		
    	$select = $form->getElement("categorie");
    	$select->addMultiOptions($values);	
    	
    	// verwijder hidden fields
    	$configFileName = Zend_Registry::getInstance()->uitval_forms->registratie;    	
    	$cfg = new Zend_Config_Ini(APPLICATION_PATH."/".$configFileName, APPLICATION_ENV);    	
    	$form = $this->removeHiddenFields($form,$cfg->hide->registratie,$this->_getCurrentSiteName());
    	
    	return $form;
    }
    
	protected function _setGewicht($data,$site_id)
    {
    	
    	$data['gewicht_bruto'] = (string) str_replace(".","@",$data['gewicht_bruto']);
		$data['gewicht_bruto'] = (string) str_replace(",",".",$data['gewicht_bruto']);
		$data['gewicht_bruto'] = (string) str_replace("@",",",$data['gewicht_bruto']);
		
		// rond af op 1 decimaal en converteer naar integer		
		
		$data['gewicht_bruto'] = (float)  round($data['gewicht_bruto'],1);
		$data['gewicht_bruto'] = (int) 10*$data['gewicht_bruto'];
		
		$gewicht_bak    		= (int) Zend_Registry::getInstance()->uitval_consts->bak->get($this->_getCurrentSiteName())->get($data['baktype'])->gewicht / 10;
   		$data['gewicht_netto']   = $data['gewicht_bruto']-($gewicht_bak*10);
   		
   		//$this->p($data,1);
   			
   		assert($data['gewicht_netto'] >= 0);
   		
   		return $data;
    	
    }
}