<?php

require_once APPLICATION_PATH.Zend_Registry::getInstance()->paths->components."BaseController.php";

class Stilstand_BaseController extends RACCMS_Component_BaseController
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
			
			$config = Zend_Registry::getInstance()->stilstand_forms->get(strtolower($this->_domain_name));
			
			if ($config === NULL)
				throw new Exception("BaseController::_getForm(".$form."): geen formulier entry gevonden voor ".$this->_domain_name." in forms.ini");
			
			assert($config !== NULL);
			
			$this->_form = new RAC_Component_Form($config,$form);
		}
		return $this->_form;
	}
	
	
	protected function _initRegistratieForm($form)
    {
    	// bepaal lijnummers van deze site
    	$lijninfo 	= Zend_Registry::getInstance()->sites->lijn->get($this->_getCurrentSiteName())->toArray();
    	foreach($lijninfo as $idx=>$lijndata)
    		$lijnen[$idx] = $lijndata['naam'];
    		
    	// bepaal categorieen en subcategirieen voor deze site    		
    	$categorien 	= Zend_Registry::getInstance()->stilstand_consts->cat->get($this->_getCurrentSiteName())->toArray();
    	$subcategorien 	= Zend_Registry::getInstance()->stilstand_consts->subcat->get($this->_getCurrentSiteName())->toArray();
    	
    	// maak array van categorieen per lijn en subcategorieen per lijn
    	foreach($lijnen as $lijnid=>$lijn){
    		
    		$cats    = Zend_Registry::getInstance()->stilstand_consts->lijn->get($lijnid);
    		$subcats = Zend_Registry::getInstance()->stilstand_consts->lijnsub->get($lijnid);
    		
    		if ($cats !== NULL)									// indien deze lijn categorieen heeft gekoppeld
    			$catperlijn[$lijnid] = explode(",",$cats);
    		else												// zoniet, lijn verwijderen uit lijnnummers array
    			unset($lijnen[$lijnid]);
    			
    		if ($subcats !== NULL)								// indien deze lijn subcategorieen heeft gekoppeld
    			$subcatsperlijn[$lijnid] = explode(",",$subcats);
    		else												// zoniet, lijn verwijderen uit lijnnummers array
    			$subcatsperlijn[$lijnid] = array();
    	}
		
		// maak filter waardes array (value#filter)
    	foreach($catperlijn as $lijn=>$cats)
    	{
    		foreach($cats as $categorie){
    			
    			if (!isset($categorien[$categorie])){
    				$this->p($cats,1);
    				throw new Exception("Categorie $categorie not set");
    			}
    			
    			$values["$categorie#$lijn"] = $categorien[$categorie];
    		}
    	}
		
		// maak filter waardes array (value#filter)
    	foreach($subcatsperlijn as $lijn=>$subcs)
    	{
    		foreach($subcs as $subc){
    			
    			if (!isset($subcategorien[$subc])){
    				$this->p($subcategorien,0);
    				throw new Exception("Subcategorie $subc not set");
    			}
    			
    			$subvalues["$subc#$lijn"] = $subcategorien[$subc];
    		}
    	}
    	
    	// tovoegen lijnnummers aan formulier element
    	$select = $form->getElement("lijnnr");
    	$select->addMultiOptions($lijnen);
    	
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
		
		// toevoegen filter waardes aan formulier element    		
    	$select = $form->getElement("subcategorie");
    	$select->addMultiOptions($subvalues);	    	
    	
    	return $form;
    }
    
	
}