<?php

require_once "GroupedTableController.php";

class Admin_OrderitemController extends Admin_GroupedTableController
{
	protected function _getTableColumnNames(){
		return array('naam'=>'orderitem_productnaam','code/nr.'=>'orderitem_productnummer');	
	}
	
	protected function _getTableColumnSorts(){
		return $this->_getTableColumnNames();	
	}
	
	protected function _getJoins(){
		return array( array('table'=>'inkooporder','field'=>'inkooporder_id','joinfields'=>array('inkooporder_datum'=>'inkooporder_datum')),
					  );
	}
	
	protected function _getAccociatedTables(){
		return NULL;
	}
	
	protected function _getGroupModel(){
		if ($this->_groupedModel == NULL)
			$this->_groupedModel = new App_Model_Inkooporder();
			
		return $this->_groupedModel;
	}
	
	protected function _getGroupControllerName(){
		return "inkooporder";
	}	
	
	protected function _getGroupControllerDisplayName(){
		return "inkooporder";
	}
	
	protected function _getGroupTableLabelField(){
		return "inkooporder_volgnummer";
	}
	
	protected function _getGroupByField(){
		return "inkooporder_id";
	}
	
	
	
	
}