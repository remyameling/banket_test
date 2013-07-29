<?php

require_once "TableController.php";
require_once APPLICATION_PATH."/components/validators/BoolString.php";

class Admin_SettingController extends Admin_TableController
{
	
	protected function _getTableColumnNames(){
		return array('naam'=>'setting_label','waarde'=>'setting_value');	
	}
	
	protected function _getTableColumnSorts(){
		return array('naam'=>'setting_label','waarde'=>'setting_value');		
	}
	
		
	protected function _getTableColumnDecoder(){
		return array();
	}

	protected function _getJoins(){
		return NULL;
	}
	
	protected function _getAccociatedTables(){
		return array();
	}
	
	protected function _getTableFilterValues(){
		return NULL;
	}
	
	protected function _update($id,$data)
	{
		unset($data['setting_type']);	
		unset($data['setting_description']);	
		
		return parent::_update($id,$data);
	}
	
	protected function _getEditForm(){
		$request      = $this->getRequest();    	
   		$id		      = $request->getParam('id',NULL);
   		assert($id !== NULL);
   		
   		$data         = $this->_getEditData($id);
   		assert($data !== NULL);
   		
   		$type = $data['setting_type'];
   		switch($type){
   			case 'bool': 	$validator = new My_Validate_BoolString();
   						 	break;
   			case 'int':  	$validator = new Zend_Validate_Int();
   						 	break;
   			case 'string':  $validator = NULL;
   						 	break; 
   			default: throw new Exception("Admin_SettingController::_getEditForm(): onbekend setting type in DB.");
   		}
		
   		$form = $this->_getForm("edit");
   		
   		if ($validator !== NULL)
   			$form->getElement("setting_value")->addValidator($validator);

   		return $form;
	}
}