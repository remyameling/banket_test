<?php

require_once "TableController.php";

class Admin_KlantController extends Admin_TableController
{
	protected function _getTableColumnNames(){
		return array('aanhef'=>'klant_sex',
					 'voornaam'=>'klant_voornaam',
					 'achternaam'=>'klant_achternaam',
					 'email'=>'klant_email');	
	}
	
	protected function _getTableColumnSorts(){
		return $this->_getTableColumnNames();
	}
	
	protected function _getTableAlphaFields(){
		return array('klant_achternaam','klant_voornaam','klant_email');	
	}
	
	protected function _getTableColumnDecoder(){
		return array('klant_sex'=>array(0=>'dhr',1=>'mevr',2=>''));
	}

	protected function _getJoins(){
		return NULL;
	}
	
	protected function _getAccociatedTables(){
		
		return array(array('title'=>'Toon alle facturen voor deze klant',
						   'controller'=>'factuur',
						   'action'=>'index',
						   'params'=>array('ff'=>'klant_id'),
						   'id_param'=>'fv',
						   'id_field'=>'id',						   		
						   'icon'=>'page_go.png'));
	}
	
	protected function _getTableFilterValues(){
		return NULL;
	}
	
	protected function _save($data){
		
		// bij opslaan leverancier, een (leeg) adres record aanmaken
		
		$adrMdl 			= new App_Model_Address();
		$id     			= $adrMdl->save(array());
		$data['address_id'] = $id;
		
		
		//print_r($data);
		//die();
		
		return parent::_save($data);		
	}
	
	protected function _update($id,$data)
	{
		// bewaar adres data		
		$address['address_streetname']  = $data['address_streetname'];
		$address['address_number']  	= $data['address_number'];
		$address['address_zipcode']  	= $data['address_zipcode'];		
		$address['address_city']  		= $data['address_city'];		 
		$address['address_country']  	= $data['address_country'];
		
		// unset address data		
		unset($data['address_streetname']);
		unset($data['address_number']);
		unset($data['address_zipcode']);		
		unset($data['address_city']);	 
		unset($data['address_country']);
		
		// update leverancier data
		$ret = parent::_update($id,$data);
		
		// fetch address_id van klant
		$data           = $this->_getModel()->fetchEntry($id);
		$address_id	 	= $data['address_id'];
		$adrMdl 		= new App_Model_Address();
		$adrMdl->update($address_id,$address);
		
		return $ret;		
	}
	
	protected function _getEditData($id)
	{
		// ophalen leveranciers en adres data
		
		$data 			= $this->_getModel()->fetchEntry($id);
		$adrMdl 		= new App_Model_Address();
		$address_data	= $adrMdl->fetchEntry($data['address_id']);
		unset($address_data['id']);
		
		return array_merge($data,$address_data);
	}
	
	public function _getTableActions(){
		
		$actions 	= parent::_getTableActions();
		
		if ($this->_isAllowed('add')){
			$actions[] 	= $this->_getActionUrl('import','import');
		}
    	
    	return $actions;
    }
	
	protected function _handleImportForm($form)
	{
		$source  	= $this->_getRequiredParam('source');
		$mdl	    = new App_Model_Klant();
		$adrMdl 	= new App_Model_Address();
				
		$config  	= Zend_Registry::getInstance()->site->customersource->get($source)->db;
		if ($config === NULL)
			throw new Exception("configuratie voor customer source $source niet gevonden in .ini file (site.ini)");
		
		$dbAdapter 	= Zend_Db::factory($config);
		
		// get customers from source
		$customers   = $mdl->importFromSource($dbAdapter,$source);
		
		// verwijder alle eerder geimporteerde produkten van deze source
		$mdl->deleteImported($source);
		
		// voeg geimporteerde klanten toe
		foreach($customers as $customer){
			
			$data 							= $customer;
			$data['klant_external_src'] 	= $source;
			
			// bewaar adres data		
			$address['address_streetname']  = $data['address_streetname'];
			$address['address_number']  	= $data['address_number'];
			$address['address_zipcode']  	= $data['address_zipcode'];		
			$address['address_city']  		= $data['address_city'];		 
			$address['address_country']  	= $data['address_country'];
			
			// unset address data		
			unset($data['address_streetname']);
			unset($data['address_number']);
			unset($data['address_zipcode']);		
			unset($data['address_city']);	 
			unset($data['address_country']);
			
			// save address
			$id     			= $adrMdl->save($address);
			$data['address_id'] = $id;
		
			$mdl->save($data);			
		}
	}
	
	public function importAction()
	{
		$ret 				= $this->_handleForm($this->_getForm('import'),NULL,"_handleImportForm",NULL);
		
		if (($ret != self::FORM_NOT_POSTED) && ($ret != self::FORM_INVALID) && ($ret != self::FORM_ACTIONSTACK))
		{
			return $this->_helper->_redirector('index','klant','admin');
	    }
	}
	
	
	
	
}