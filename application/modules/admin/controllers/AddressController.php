<?php

require_once "GroupedTableController.php";

class Admin_AddressController extends Admin_GroupedTableController
{
	protected function _getTableColumnNames(){
		return array('default'=>'address_default','klantnaam'=>'klantnaam','klantnaam'=>'klantnaam','straat'=>'address_streetname',
					 'nummer'=>'address_number','postcode'=>'address_zipcode','plaats'=>'address_city','land'=>'land');	
	}
	
	protected function _getTableFilterValues(){
		return NULL;
	}
	
	protected function _getTableColumnSorts(){
		return array('default'=>'address_default','klantnaam'=>'klantnaam','straat'=>'address_streetname',
				     'nummer'=>'address_number','postcode'=>'address_zipcode','plaats'=>'address_city');	
	}
	
	protected function _getTableColumnDecoder(){
		return array('address_default'=>array(0=>'nee',1=>'ja'));
	}

	protected function _getJoins(){
		return array( array('table'=>'member','field'=>'member_id','joinfields'=>array('klantnaam'=>'member_fullname')),
				      array('table'=>'country', 'field'=>'address_country','joinfields'=>array('land'=>'country_name')));
	}
	
	protected function _getAccociatedTables(){
		return array();
	}
	
	protected function _getGroupModel(){
		if ($this->_groupedModel == NULL)
			$this->_groupedModel = new App_Model_Customer();
			
		return $this->_groupedModel;
	}
	
	protected function _getGroupControllerName(){
		return "member";
	}	
	
	protected function _getGroupControllerDisplayName(){
		return "leden";
	}
	
	protected function _getGroupTableLabelField(){
		return "member_fullname";
	}
	
	protected function _getGroupByField(){
		return "member_id";
	}
	
	protected function checkDeleteAllowed($id)
	{
		$data = $this->_getModel()->fetchEntry($id);
		assert($data !== NULL);
		
		return "niet toegestaan";		
	}
	
	protected function _save($data){
		
		// bij opslaan eerste adres van een klant, default maken
		
		$num_addresses = $this->_getModel()->fetchNumAddresses($data['member_id']);
		
		if ($num_addresses == 0)
			$data['address_default'] = 1;
		else
			$data['address_default'] = 0;
		
		return parent::_save($data);		
	}
	
	protected function _update($id,$data)
	{
		// customer id is disabled; normaliter volstaat unsetten hierban echter:
		// customer_id is nodig is Model::resetDefault. Dus customer_id ophalen en opnieuw zetten
		
		$old_data = $this->_getModel()->fetchEntry($id);
		$data['member_id'] = $old_data['member_id'];
		
		// bij updaten en wanneer default is gezet, default eerst resetten
		
		if ($data['address_default'] == 1)
			$this->_getModel()->resetDefault($data['member_id']);		
			
		return parent::_update($id,$data);
	}
	
	
}