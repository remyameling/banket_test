<?php

require_once "GroupedTableController.php";

class Admin_LeverancierproductController extends Admin_GroupedTableController
{
	protected function _getTableColumnNames(){
		return array('productnaam'=>'product_naam');	
	}
	
	protected function _getTableColumnSorts(){
		return $this->_getTableColumnNames();	
	}
	
	protected function _getJoins(){
		return array( array('table'=>'leverancier','field'=>'leverancier_id','joinfields'=>array('leverancier_naam'=>'leverancier_naam')),
					  array('table'=>'product','field'=>'product_id','joinfields'=>array('product_naam'=>'product_naam')));
	}
	
	protected function _getAccociatedTables(){
		return NULL;
	}
	
	protected function _getGroupModel(){
		if ($this->_groupedModel == NULL)
			$this->_groupedModel = new App_Model_Leverancier();
			
		return $this->_groupedModel;
	}
	
	protected function _getGroupControllerName(){
		return "leverancier";
	}	
	
	protected function _getGroupControllerDisplayName(){
		return "leverancier";
	}
	
	protected function _getGroupTableLabelField(){
		return "leverancier_naam";
	}
	
	protected function _getGroupByField(){
		return "leverancier_id";
	}
	
	protected function _initAddForm(){
    	$product_id = $this->_getRequiredParam('product_id');
    	return array('product_id'=>$product_id);
    }
    
	protected function _redirectAfterEdit($id)
	{
		$product_id = $this->_getRequiredParam('product_id');
		$role 		= App_Auth_Auth::getInstance()->getRole();
		$resource 	= "admin_product";
		
		if (Zend_Registry::getInstance()->acl->isAllowed($role,$resource,"edit"))
		{
			return $this->_helper->redirector('edit','product','admin',array('id'=>$product_id,'t'=>'leverancier'));
		}
		else
		{
			return $this->_helper->redirector('index','index');
		}
    }
	
	
}