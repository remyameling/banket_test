<?php

require_once "TableController.php";

class Admin_LeverancierController extends Admin_TableController
{
	protected function _getTableColumnNames(){
		return array('naam'=>'leverancier_naam');	
	}
	
	protected function _getTableColumnSorts(){
		return $this->_getTableColumnNames();
	}
	
	protected function _getTableColumnDecoder(){
		return array();
	}

	protected function _getJoins(){
		return NULL;
	}
	
	protected function _getAccociatedTables(){
		
		return array(array('title'=>'Toon alle produkten van deze leverancier',
						   'controller'=>'user',
						   'action'=>'index',
						   'params'=>array('ff'=>'group_id'),
						   'id_param'=>'fv',
						   'id_field'=>'id',						   		
						   'icon'=>'page_go.png'),
					array('title'=>'Toon alle inkooporders voor deze leverancier',
						   'controller'=>'inkooporder',
						   'action'=>'index',
						   'params'=>array('ff'=>'leverancier_id'),
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
		
		// fetch address_id van leverancier
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
	
	
	
	
}