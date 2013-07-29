<?php

require_once "GroupedTableController.php";

class Admin_InkooporderController extends Admin_GroupedTableController
{
	protected function _getTableColumnNames(){
		return array('status'=>'inkooporder_status','inkooporder_volgnummer'=>'inkooporder_volgnummer','inkooporder_datum'=>'inkooporder_datum');	
	}
	
	protected function _getTableColumnSorts(){
		return $this->_getTableColumnNames();	
	}
	
	protected function _getTableColumnDecoder(){
		return array('inkooporder_status'=>Zend_Registry::getInstance()->consts->inkooporder->status->toArray());
	}
	
	protected function _getJoins(){
		return array( array('table'=>'user','field'=>'user_id','joinfields'=>array('user_name'=>'user_name')));
	}
	
	protected function _getAccociatedTables(){
		
		return array(array('title'=>'Toon alle items van deze order',
						   'controller'=>'orderitem',
						   'action'=>'index',
						   'params'=>array('ff'=>'inkooporder_id'),
						   'id_param'=>'fv',
						   'id_field'=>'id',						   		
						   'icon'=>'page_go.png'));
	}
	
	protected function _getGroupModel(){
		if ($this->_groupedModel == NULL)
			$this->_groupedModel = new App_Model_User();
			
		return $this->_groupedModel;
	}
	
	protected function _getGroupControllerName(){
		return "user";
	}	
	
	protected function _getGroupControllerDisplayName(){
		return "user";
	}
	
	protected function _getGroupTableLabelField(){
		return "user_alias";
	}
	
	protected function _getGroupByField(){
		return "user_id";
	}
	
	
	
	
}