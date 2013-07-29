<?php

require_once "GroupedTableController.php";

abstract class Admin_SortableTableController extends Admin_GroupedTableController 
{
	protected function _save($data){
		
		$sort_field   		= $this->_getItemOrderField();
		$filter_field 		= $this->_getItemOrderFilter();
		
		if (!isset($data[$filter_field]))	// als filter field niet op het formulier aanwezig was
		{									// dan probeer filter value via request params op te halen
											// en voeg to aan data
			$request 				= $this->getRequest();
			$filter_value   		= $request->getParam($filter_field,NULL);	
			$data[$filter_field]	= $filter_value;	
			
			if ($filter_value === NULL)
				throw new Exception("Admin_SortableTableController::_save() waarde voor $filter_field niet op formulier en niet in request params.");
		}
		
		
		$filter_value 		= $data[$filter_field];
		$next_sortkey 		= $this->_getModel()->nextSortValue($sort_field,$filter_field,$filter_value);
		$data[$sort_field]  = $next_sortkey;
		
		return parent::_save($data);		
	}
	
	protected function _delete($id){
		assert($id !== NULL);
		
		$sort_field   		= $this->_getItemOrderField();
		assert($sort_field !== NULL);
		
		$filter_field 		= $this->_getItemOrderFilter();
		assert($filter_field !== NULL);
		
		$this->_getModel()->delete($id,$sort_field,$filter_field);
		return $ret;		
	}
	
	protected function getIndexScript()
	{
		return 'table/sort';
	}
	
    public function indexAction() 
    {
    	$request      = $this->getRequest();
    	
    	$order        = $request->getParam('order',  NULL);
   		$sort    	  = $request->getParam('sort',   'asc');
   		$filterfield  = $request->getParam('ff', 	 NULL);
   		$filtervalue  = $request->getParam('fv',     NULL);
   		$page		  = $request->getParam('page',   1);
   		$num_items    = $request->getParam('ic',	 Zend_Registry::getInstance()->session->num_items_per_page);   		
   		$filter       = $this->_getFilter($filterfield,$filtervalue);
   		
   		Zend_Registry::getInstance()->session->num_items_per_page = $num_items;
   		
   		$this->_redirectIfNotFiltered();
   		
   		if ($this->getRequest()->isPost()){
   			
   			
   			
   			$this->_redirector = $this->_helper->getHelper('Redirector');
   			return $this->_redirector->gotoSimple('index',$this->_controller,'admin',$request->getParams());
   		}
   			
   		
    	$entries      = $this->_fetchEntries($order,$sort,$filter);  
   		$paginator    = $this->_getPaginator($entries,$page,$num_items);
   		
   		$this->view->paginator 			= $paginator;
   		$this->view->controller 		= $this->_controller;
   		$this->view->cols       		= $this->_getTableColumnNames();
   		$this->view->sorts      		= $this->_getTableColumnSorts();
   		$this->view->decoder      		= $this->_getTableColumnDecoder();
   		$this->view->domain_displayname	= $this->_getDisplayName();   		
   		$this->view->filtervalues   	= $this->_getTableFilterValues();
   		$this->view->associations		= $this->_getAccociatedTables();
   		$this->view->order				= $order;
   		$this->view->sort				= $sort;
   		$this->view->tableactions		= $this->_getTableActions();
   		$this->view->rowactions         = $this->_getRowActions();
   		$this->view->bulk_actions	    = $this->_getBulkActions();
   		$this->view->ff   				= $filterfield;
	   	$this->view->fv   		    	= $filtervalue;
	   	$this->view->ic   		    	= $num_items;
	   	$this->view->page  		    	= $page;
	   	
	   	if (count($entries) > 0){
	   		
	   		$first        					= $entries[0];
	   		$last         					= $entries[count($entries)-1];
   			$this->view->first_item_id  	= $first['id'];
	   		$this->view->last_item_id   	= $last['id'];
	   	}
	   	else{
	   		$this->view->first_item_id  	= 0;
	   		$this->view->last_item_id   	= 0;
	   	}
   		
   		
   		$this->view->addScriptPath(dirname(__FILE__)."/../views/scripts/pagination");    
        $this->render($this->getIndexScript(),NULL,true);
    }
    
    private function _getSortKey($id){
    	$mdl 	= $this->_getModel();
    	$data	= $mdl->fetchEntry($id);
    	return  $data[$this->_getItemOrderField()];
    }
    
    protected function _getLowestSortKey($ids){
    	
    	if (count($ids > 0)){
    	   	
    		foreach($ids as $id)
    			$keys[$id] = $this->_getSortKey($id);
    		
    		asort($keys);
    		$key = array_shift($keys);
    	   	return $key;
    	}
    	else
    	{
    		die("empty array");
    	}
    }
    
	
    
    protected function _updateSortValue($id,$sort_value,$sort_field){
    	
    	$mdl 	= $this->_getModel();
    	//$data	= $mdl->fetchEntry($id);
    	$data[$sort_field] = $sort_value;
    	
    	
    	$mdl->update($id,$data);
    	
    }
    
    public function savesortAction(){
    	
    	$request      = $this->getRequest();
    	$sort_values  = $request->getParam('save_sort_val');
    	$ff  		  = $request->getParam('ff',  NULL);
    	$fv  		  = $request->getParam('fv',  NULL);
    	$page  		  = $request->getParam('page',1);
    	
    	$this->_redirector = $this->_helper->getHelper('Redirector');
    	
    	if ($sort_values == "0")	// niets veranderd
    	{	
    		return $this->_redirector->gotoSimple('index',
    											  $this->_controller,
    											  'admin',array('fv'=>$fv,'ff'=>$ff,'page'=>$page));
    	}
    	else
    	{
    		$sort_values = explode("&",$sort_values);
    		unset($sort_values[0]);
    		foreach($sort_values as $key=>$sort_value)
    			$values[$key] = substr($sort_value,7);
    		
    		$lowest = $this->_getLowestSortKey($values);
    		foreach($values as $id){
    			$this->_updateSortValue($id,$lowest,$this->_getItemOrderField());
    			$lowest++;
    		}
    		return $this->_redirector->gotoSimple('index',
    											  $this->_controller,
    											  'admin',array('fv'=>$fv,'ff'=>$ff,'page'=>$page));
    		
    	}    	
    }
    
	protected function _getPreviousRecord($this_id){

		$field 		 = $this->_getGroupByField();
		$data        = $this->_getModel()->fetchEntry($this_id);
		assert(isset($data[$field]));
		
		$value	     = $data[$field];
				
		return $this->_getModel()->fetchPrevious($this_id,"$field = $value",$this->_getItemOrderField(),$data[$this->_getItemOrderField()]);		
	}
	
	protected function _getNextRecord($this_id){	

		$field 		 = $this->_getGroupByField();
		$data        = $this->_getModel()->fetchEntry($this_id);
		assert(isset($data[$field]));
		
		$value	     = $data[$field];
		
		return $this->_getModel()->fetchNext($this_id,"$field = $value",$this->_getItemOrderField(),$data[$this->_getItemOrderField()]);		
	}
    
	public function editsortAction() 
    {
    	$request      = $this->getRequest();
    	$item_id	  = $request->getParam('id',   		NULL);
    	$direction    = $request->getParam('dir', 		NULL);
    	$ff           = $request->getParam('ff', 		NULL);
    	$fv			  = $request->getParam('fv', 		NULL);
    	
    	if (($ff == NULL) || ($fv == NULL))
    		throw new Exception("SortableTableController::editsortAction(): ff or fv is null.");

    	if ($item_id !== NULL){
    		if (($direction == 'up') || ($direction == 'down')){
    			
    			$this->_getModel()->editSort($item_id,$direction,$this->_getItemOrderField(),$ff,$fv);
    			

    			$this->_redirector = $this->_helper->getHelper('Redirector');
   				return $this->_redirector->gotoSimple('index',$this->_controller,'admin',array('fv'=>$fv,'ff'=>$ff));
    		}
    		else
    			throw new Exception("TableController::editsortAction(): invalid parameter dir ($direction)detected");	
    	}
    	else
    		throw new Exception("TableController::editsortAction(): invalid parameter id detected");
    }
}