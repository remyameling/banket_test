<?php

require_once "TableController.php";

abstract class Admin_GroupedTableController extends Admin_TableController 
{
	protected $_groupedModel = NULL;
	
	abstract protected function _getGroupModel();
	abstract protected function _getGroupControllerName();	
	abstract protected function _getGroupControllerDisplayName();
	abstract protected function _getGroupTableLabelField();
	abstract protected function _getGroupByField();
	
	protected function _getTableIndexAction(){
    	
    	$request      	= $this->getRequest();
    	$id				= $request->getParam('id');
    	
    	
    	$url_params     = array('module'=>'admin',
    					   		'controller'=>$this->_controller,
    					   		'action'=>'index');
    	if ($id !== NULL)
    	{
    		$entry 			= $this->_getModel()->fetchEntry($id);
    		$ff			    = $this->_getGroupByField();
			$fv				= $entry[$ff];
			$url_params['ff'] = $ff;
			$url_params['fv'] = $fv;
    	}
    	
    	return array('actionname'=>"group",
    				 'menuname'=>"overzicht",
    				 'urlparams' =>$url_params);	
    }
    
	public function _getTableActions()
	{
    	
    	$request      	= $this->getRequest();
    	$id				= $request->getParam('id');
    	$current_action	= $request->getParam('action');
    	
    	$actions = parent::_getTableActions();
    	
    	if (($this->_isAllowed('index')) && ($current_action == 'index'))
    	{
    		$pactions[]   = array('actionname'=>'parent',
    							'menuname'=>'Terug naar '.$this->_getGroupControllerDisplayName(),
    							'urlparams'=>array('module'=>'admin','controller'=>$this->_getGroupControllerName(),'action'=>'index'));
    		return array_merge($pactions,$actions);
    	}
    		
    	return $actions;
    	
    }
	
	protected function _fetchGroupValues(){
		
		$model = $this->_getGroupModel();
		
		return $model->fetchWhereGroupHasElements($this->_getModel()->getTableName(),$this->_getGroupByField());
		
	}
	
	
		
	protected function _getTableFilterValues(){
		
		$request  = $this->getRequest();
		$fv       = $request->getParam('fv',     NULL);
		$ff       = $request->getParam('ff',     NULL);
		
		$entries  = $this->_fetchGroupValues();
		
		
		
		$names    = array();
		
		foreach($entries as $entry)
			$names[$entry['id']] = $entry[$this->_getGroupTableLabelField()];
			
		$ret = array('filterfield'  =>$this->_getGroupByField(),
					 'filtervalues' =>$names,
					 'filterlabel'  =>'Kies '.$this->_getGroupControllerDisplayName().':');
		
		if ($ff == $this->_getGroupByField()){			
			$entry	 			  	= $this->_getGroupModel()->fetchEntry($fv);
			$ret['filtercurrent'] 	= $entry['id'];			
		}
		else{			
			$entry 					= $this->_getGroupModel()->fetchDefault();
			$ret['filtercurrent'] 	= $entry['id'];
		}
		
		
		
		
		return $ret;
	}
	
	protected function _getDefaultFilter(){
		
		$model   = $this->_getGroupModel();	
		$default = $model->fetchDefault();
		
		if ($default !== NULL)
			return array(array($this->_getGroupByField()=>$default['id']));
		else
			return NULL;
	}
	
	
	
	protected function _getAddForm(){
		
		$request      = $this->getRequest();
		$ff		      = $request->getParam($this->_getGroupByField(),NULL);
		
		$form 		= $this->_getForm("add");
		$defaults 	= array($this->_getGroupByField()=>$ff);
		$form->setDefaults($defaults);
		return $form;
	}
	
	/*
	protected function _handleEditForm($form){
		$group   = $this->_getGroupByField();
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		$data    = $form->getValues();
		unset($data['save']);
		unset($data['back']);
		//unset($data[$group]);
		
		return $this->_update($id,$data);   	
    }
    */
	
	protected function _getDefaultActionUrlParams($action_name){
		
		$request      = $this->getRequest();
		$ff		      = $request->getParam('ff',NULL);
		$fv		      = $request->getParam('fv',NULL);
		$group        = $this->_getGroupByField();
				
		if (($action_name == 'add') && ($ff == $group) && ($fv !== NULL)){			
			return array('module'=>'admin','controller'=>$this->_controller,'action'=>$action_name,$group=>$fv);
		}
		elseif (($action_name == 'index') && ($ff == $group) && ($fv !== NULL)){
			return array('module'=>'admin','controller'=>$this->_controller,'action'=>$action_name,'ff'=>$ff,'fv'=>$fv);
		}
		elseif (($action_name == 'sort') && ($fv !== NULL)){
			return array('module'=>'admin','controller'=>$this->_controller,'action'=>$action_name,'ff'=>$group,'fv'=>$fv);
		}
		else
			return array('module'=>'admin','controller'=>$this->_controller,'action'=>$action_name);
    }
    
	protected function _redirectAfterEditToEdit($id){
		
		$request      = $this->getRequest();
		$ff		      = $request->getParam('ff',NULL);
		$fv		      = $request->getParam('fv',NULL);
		
		print_r($request);
		
		if (($ff !== NULL) && ($fv !== NULL))
			$params = array('id'=>$id,'ff'=>$ff,'fv'=>$fv);
		else
			$params = array('id'=>$id);	
		
    	return $this->_helper->redirector('edit',$this->_controller,'admin',$params);
    }
    
	protected function _getEditFormAction($id){		
		$request      = $this->getRequest();
		$ff		      = $request->getParam('ff',NULL);
		$fv		      = $request->getParam('fv',NULL);
		
		$params['id'] = $id;		
		if (($ff !== NULL) && ($fv !== NULL)){
			$params['ff'] = $ff;
			$params['fv'] = $fv;
		}
			
		return $this->_helper->url->direct('edit',null,null,$params);
	}
    
	protected function _redirectAfterEdit($id){		
		$group  = $this->_getGroupByField();
		$entry 	= $this->_getEditData($id);
		$id 	= $entry[$group];
		
		$role 		= App_Auth_Auth::getInstance()->getRole();
		$resource 	= "admin_".$this->_controller;
		
		if (Zend_Registry::getInstance()->acl->isAllowed($role,$resource,"index"))
		{
			return $this->_helper->redirector('index',$this->_controller,'admin',array('ff'=>$group,'fv'=>$id));
		}
		else
		{
			return $this->_helper->redirector('index','index');
		}
		
    	
    }
    
	protected function _redirectAfterAdd($id){
		$group  		= $this->_getGroupByField();
		$entry 			= $this->_getEditData($id);
		$fv 			= $entry[$group];
		
    	return $this->_helper->redirector('edit',$this->_controller,'admin',array('id'=>$id,'ff'=>$group,'fv'=>$fv));
    }
    
	protected function _redirectAfterDelete(){
		$group  	= $this->_getGroupByField();
		$request   	= $this->getRequest();
		$id 		= $request->getParam('fv',NULL);
		
		if ($id !== NULL)
			return $this->_helper->_redirector('index',$this->_controller,'admin',array('page'=>$page,'ff'=>$group,'fv'=>$id));
		else
			return $this->_helper->_redirector('index',$this->_controller,'admin');
    }
    
	protected function _getIndexViewTabPages($id){
    	return array();
    }
	
	private function _getIndexViewTabs(){
		
		$request      = $this->getRequest();
		$id           = $request->getParam('fv',NULL);
		
		$container = new Zend_Navigation();
        $container->addPages($this->_getIndexViewTabPages($id));
        
        return $container;
	}
	
	protected function _redirectIfNotFiltered()
	{	
		// redirect naar eerste "niet-lege" group, indien param 'fv' en 'ff' niet aanwezig zijn.
		// deze function wordt aangeroepen vanuit Admin_SortableTableController en Admin_GroupedTableController
		// index action
		
		$request      = $this->getRequest();
    	
   		$filterfield  = $request->getParam('ff', 	 NULL);
   		$filtervalue  = $request->getParam('fv',     NULL);
		
		
		if ($filterfield === NULL){		// indien 'fv' en 'ff' niet aanwezig: redirect naar eerste niet lege:
   										// groupded table controller index actie laat alleen groupen zien (in menu filter) waarvoor
   										// waardes gevuld zijn
   			$entries	  = $this->_getGroupModel()->fetchWhereGroupHasElements($this->_getModel()->getTableName(),$this->_getGroupByField());
   			
   			$params		  = $request->getParams();
   			
   			if (count($entries) > 0){
   				$params['fv'] = $entries[0]['id'];
   			}
   			else{
   				
   				$entry 		  = $this->_getGroupModel()->fetchDefault();
   				$params['fv'] = $entry['id'];   				
   				
   			}
   			$params['ff'] = $this->_getGroupByField();
			
   			return $this->_helper->getHelper('Redirector')->gotoSimple('index',$this->_controller,'admin',$params);
   		}
		
	}
	
	public function indexAction() 
    {
    	$this->_redirectIfNotFiltered();
    	
    	$this->view->tabs   	  		= $this->_getIndexViewTabs();
    	
    	parent::indexAction();
    }
    
	protected function _getPreviousRecord($this_id){
		
		$field 		 = $this->_getGroupByField();
		$data        = $this->_getModel()->fetchEntry($this_id);
		assert(isset($data[$field]));
		
		$value	     = $data[$field];
		
		return $this->_getModel()->fetchPrevious($this_id,"$field = $value");		
	}
	
	protected function _getNextRecord($this_id){	

		$field 		 = $this->_getGroupByField();
		$data        = $this->_getModel()->fetchEntry($this_id);
		assert(isset($data[$field]));
		
		$value	     = $data[$field];
		
		return $this->_getModel()->fetchNext($this_id,"$field = $value");		
	}	
}