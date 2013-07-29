<?php

require_once "BaseController.php";

abstract class Admin_TableController extends Admin_BaseController 
{
	protected $_model 		= NULL;
	protected $_domain_name = NULL;
	protected $_controller  = NULL;
	protected $_model_class	= NULL;
	
	const NUM_ITEMS_PER_PAGE   = 20;
	
	public function __construct(Zend_Controller_Request_Abstract $request,
                                Zend_Controller_Response_Abstract $response,
                                array $invokeArgs = array())
	{
		// if $_domain_name nog niet is geinitialiseerd,
		// dan initialiseer domain_name met naam van controller
		
		$parts 			 = explode("_",get_class($this));
		$controller_name = $parts[count($parts)-1];
		
		
		if ($this->_domain_name === NULL)			
			$this->_domain_name = substr($controller_name,0,strlen($controller_name)-strlen("Controller"));		
		
		if ($this->_model_class === NULL)
			$this->_model_class = "App_Model_".$this->_domain_name;

		$this->_controller = strtolower($this->_domain_name);
		
		// set num items per page
		
		if (!isset(Zend_Registry::getInstance()->session->num_items_per_page))
			Zend_Registry::getInstance()->session->num_items_per_page = self::NUM_ITEMS_PER_PAGE;
			
		parent::__construct($request,$response,$invokeArgs);		
	}
	
	abstract protected function _getTableColumnNames();
	abstract protected function _getTableColumnSorts();
	abstract protected function _getJoins();
	abstract protected function _getAccociatedTables();
	abstract protected function _getTableFilterValues();
	
	protected function _getTableAlphaFields(){
		return array();
	}
	
	protected function _getDisplayName(){

		if (Zend_Registry::getInstance()->admin_const->controller->get($this->_controller) === NULL)
			throw new Exception("TableController::_getDisplayName(): domaindisplayname voor controller ".$this->_controller." niet gevonden in admin const.ini");
		
		return Zend_Registry::getInstance()->admin_const->controller->get($this->_controller)->domaindisplayname;
		
		return $this->_domain_name;
	}
	
	
	
	protected function _getTableColumnDecoder(){
		return array();
	}
	
	protected function _getBulkActions(){
		
		$actions = array();
		if ($this->_isAllowed('delete'))
			$actions[]   = "Verwijderen";
    		
		return $actions;
	}
	
	protected function _getModel()
    {
        if (null === $this->_model)
		{
			$this->_model = new $this->_model_class();
        }
        return $this->_model;
    }
    
    //
    // retouneert het sorteerveld (indien aanwezig) van deze tabel
    //
    protected function _getItemOrderField(){
    	return NULL;
    }
    
    //
    // retouneert het groepeerveld, indien van toepassing waarop
    // items in de tabel gesorteerd dienen te worden (bijv. menu_id voor menu_items)
    //
    protected function _getItemOrderFilter(){
    	return NULL;
    }
    
    protected function _isAllowed($actionname){
    	
    	$role 		= App_Auth_Auth::getInstance()->getRole();
    	$resource 	= "admin_".$this->_controller;
    	return Zend_Registry::getInstance()->acl->isAllowed($role,$resource,$actionname);
    }
    
    protected function _getDefaultActionUrlParams($action_name){
    	return array('module'=>'admin','controller'=>$this->_controller,'action'=>$action_name);
    }
    
    protected function _getActionUrl($action_name,$menu_name){
    	
    	return array('actionname'=>$action_name,'menuname'=>$menu_name,'urlparams'=>$this->_getDefaultActionUrlParams($action_name));
    }
    
    protected function _getTableIndexAction(){
    	
    	return $this->_getActionUrl('index','overzicht');
    	
    }
    
    //
    // retouneert array met extra menu opties voor deze tabel
    //
    public function _getTableActions(){
    	
    	// default actions : add, index (if allowed)
    	
    	$request      	= $this->getRequest();
    	$id				= $request->getParam('id');
    	$current_action	= $request->getParam('action');
    	
    	$actions 		= NULL;
    	
    	if ($this->_isAllowed('add')){
    		
    		$actions[]   = $this->_getActionUrl('add','toevoegen');
    	}
    	if (($this->_isAllowed('index')) && ($current_action != 'index'))
    	{
    		$actions[]   = $this->_getTableIndexAction();
    	}
    	
    	return $actions;
    }
    
	protected function _getRowActions(){
		return array(array('action'=>'edit','icon'=>'edit.png','title'=>'bewerken','privilege'=>'edit'),
				     array('action'=>'delete','icon'=>'delete.png','title'=>'verwijderen','privilege'=>'delete'));
	}
    
	protected function _getAddForm(){
		return $this->_getForm("add");
	}
	
	protected function _getEditForm(){
		return $this->_getForm("edit");
	}
	
    protected function _fetchEntries($order,$sort,$filter){
    	
    	return $this->_getModel()->fetchEntries($order,$sort,$this->_getJoins(),$filter,true);	
	}
	
	protected function _getDefaultFilter(){
		return NULL;
	}
	
	
	
	protected function _getFilter($field,$value)
	{
		if (($field !== NULL) && ($value !== NULL))
			return array(array($field=>$value));
		else
			return $this->_getDefaultFilter();
	}
	
	protected function _getPaginator($entries,$page,$items_per_page)
	{
	   if ($items_per_page == 0)
	   		$items_per_page = count($entries);	
		
	   Zend_Paginator::setDefaultScrollingStyle('Sliding');
	   Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
	   
	   $paginator = Zend_Paginator::factory($entries);
	   $paginator->setItemCountPerPage($items_per_page);
	   $paginator->setCurrentPageNumber($page);
	   $paginator->setView($this->view);
	   
	   return $paginator;
	}
	
	protected function getIndexScript()
	{
		return 'table/index';
	}
	
	protected function getAlfaPaginatorField($field,$paginator)
	{
		$fields   = $this->_getTableAlphaFields();
		
		
		
		if (($field !== NULL) && (in_array($field,$fields)))
		{
			$pageData       = $paginator->getPages();
			$numPages       = $pageData->pageCount;
			
			if ($numPages > 10)
			{
				$refs = array();
				for($i=1;$i<=$numPages;$i++)
				{
					$items 		= $paginator->getItemsByPage($i);
					$char       = strtolower(substr($items[0][$field],0,1));
					
					if ((!isset($refs[$char])) && ($char != " "))
						$refs[$char] = $i-1; 			
				}
				
				foreach($refs as $character=>$page_num)
					$hrefs[$character] = "/admin/".$this->_controller."/index/order/$field/sort/asc/page/$page_num";
				
				return $hrefs;
			}
			else
				return NULL;
		}
		else
		{
			
			return NULL;
		}
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
   		
   		Zend_Registry::getInstance()->session->num_items_per_page = $num_items;
   		
   		$filter       = $this->_getFilter($filterfield,$filtervalue);
   		
   		if ($this->getRequest()->isPost()){
   			
   			$params = $request->getParams();
   			   			
   			$this->_redirector = $this->_helper->getHelper('Redirector');
   			return $this->_redirector->gotoSimple('index',$this->_controller,'admin',$params);
   		}
   			
   		
    	$entries      = $this->_fetchEntries($order,$sort,$filter);  
   		$paginator    = $this->_getPaginator($entries,$page,$num_items);
   		
   		
   		$this->view->alfa_paginator 	= $this->getAlfaPaginatorField($order,$paginator);
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
   		$this->view->ic   		    	= $num_items;
   		
   		$this->view->addScriptPath(dirname(__FILE__)."/../views/scripts/pagination");    
        $this->render($this->getIndexScript(),NULL,true);
    }
    
    //
    // saves new object
    //
	protected function _save($data){
		
		unset($data['frmid']);
		
		$model 	= $this->_getModel();
		$ret    = $model->save($data);
		return $ret;		
	}
	
	//
	// update existing object
	//
	protected function _update($id,$data)
	{
		unset($data['frmid']);
		
		$model = $this->_getModel();
		return $model->update($id,$data);
	}
	
	//
	// delete object
	//
	protected function _delete($id)
	{
		return $this->_getModel()->delete($id);
	}
	
	
	
	//
	// get object data
	//
	protected function _getEditData($id){
		
		return $this->_getModel()->fetchEntry($id);
	}
    
    protected function _handleAddForm($form){
    	
    	$ret = $this->_save($form->getValues());
    	return $ret;   	
    }
    
	protected function _getFilenameFieldname(){
		return NULL;
	}
	
	protected function _getFilemimetypeFieldname(){
		return NULL;
	}
	
	protected function _getFileorgnameFieldname(){
		return NULL;
	}
	
	protected function _getFilenamePrefix(){
		return NULL;
	}
	
	protected function _getUploadedFileUrl($file_id)
    {
		return "";    	
	}
	
	protected function _hasFileField(){
		if ($this->_getFilenameFieldname() !== NULL)
			return true;
		else
			return false;
	}
    
	protected function _handleEditForm($form){
		
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		$data    = $form->getValues();
		unset($data['save']);
		unset($data['back']);
		
		if ($this->_hasFileField()){
			
			if (isset($data[$this->_getFilenameFieldname()]))
			{
				if ($data[$this->_getFilenameFieldname()] != "")
				{
					$this->_handleUpload($form,$id,$this->_getFilenamePrefix(),
										 $data[$this->_getFilenameFieldname()],
										 $this->_getFilenameFieldname(),
										 $this->_getFileorgnameFieldname(),
										 $this->_getFilemimetypeFieldname());
							
			
				}
			}
			
			unset($data[$this->_getFilenameFieldname()]);
			unset($data[$this->_getFileorgnameFieldname()]);
			unset($data[$this->_getFilemimetypeFieldname()]);
			
		}
		
		return $this->_update($id,$data);   	
    }
    
	protected function _redirectAfterAdd($id){
		
		$ret = $this->_helper->redirector('edit',$this->_controller,'admin',array('id'=>$id));
		return $ret;
    }
    
    protected function _initAddForm(){
    	return NULL;
    }
    
	public function addAction(){
		$request = $this->getRequest();
		$back    = $request->getParam('back',false);
		
		$ret = $this->_handleForm($this->_getAddForm(),$this->_initAddForm(),"_handleAddForm");
		
		if (($ret != self::FORM_NOT_POSTED) && ($ret != self::FORM_INVALID))
		{
			if ($ret === NULL)
				throw new Exception("TableController::addAction(): geen waarde geretouneerd door save actie.");
			
			if ($this->_isAllowed('edit'))
			{
				if ($back)
	    			return $this->_redirectAfterEdit($ret);
	    		else
	    			return $this->_redirectAfterAdd($ret);    			
			}
    		else
    			return $this->_helper->redirector('index',$this->_controller);
		}
		
		$this->view->controller 		= $this->_controller;
		$this->view->tableactions		= $this->_getTableActions();
		$this->view->domain_displayname	= $this->_getDisplayName();
				
		return $this->render('table/add',null,true);
	}
	
	protected function _redirectAfterEdit($id){
		
		$role 		= App_Auth_Auth::getInstance()->getRole();
		$resource 	= "admin_".$this->_controller;
		
		if (Zend_Registry::getInstance()->acl->isAllowed($role,$resource,"index"))
		{
			return $this->_helper->redirector('index',$this->_controller);
		}
		else
		{
			return $this->_helper->redirector('index','index');
		}
    }
    
	protected function _redirectAfterEditToEdit($id){
    	return $this->_helper->redirector('edit',$this->_controller,'admin',array('id'=>$id));
    }
    
    protected function _getEditFormAction($id){
    	return $this->_helper->url->direct('edit',null,null,array('id'=>$id));
    }
    
    protected function _getEditViewTabPages($id){
    	return array();
    }
    
	protected function _getEditViewTabs($id){
		$container = new Zend_Navigation();
        $container->addPages($this->_getEditViewTabPages($id));
        return $container;
	}
	
	protected function _getPreviousRecord($this_id){		
		return $this->_getModel()->fetchPrevious($this_id);		
	}
	
	protected function _getNextRecord($this_id){		
		return $this->_getModel()->fetchNext($this_id);	
	}
	
	public function deletefileAction()
    {
    	if ($this->_hasFileField())
    	{
    		$id 	= $this->_getRequiredParam('id');
    	
    		$this->_deleteUploadedFile($this->_getModel(),
    								   $id,
    								   $this->_getFilenameFieldname(),
    								   $this->_getFilemimetypeFieldname(),
    								   $this->_getFileorgnameFieldname());
    	}
    	    	
    	return $this->_ReturnReferal();
    } 
    
    protected function editRender()
    {
    	return $this->render('table/edit',NULL,true);
    }
	
	public function editAction()
    {
    	$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		$back    = $request->getParam('back',false);
		
		
		
		if ($id !== NULL){
			
			$ret = $this->_handleForm($this->_getEditForm(),
    								  $this->_getEditData($id),
    								  "_handleEditForm",
    								  $this->_getEditFormAction($id));
    								  
			if (($ret != self::FORM_NOT_POSTED) && ($ret != self::FORM_INVALID) && ($ret != self::FORM_ACTIONSTACK))
			{
				if (!$back)
	    			return $this->_redirectAfterEditToEdit($id);
	    		else
	    			return $this->_redirectAfterEdit($id);
	    			
			}
			
			
			
			if ($this->_hasFileField()){
				
				
				
				$data = $this->_getEditData($id);
				if ((isset($data[$this->_getFilenameFieldname()])) && ($data[$this->_getFilenameFieldname()] != ""))
				{
					switch($data[$this->_getFilemimetypeFieldname()])
					{
						case Zend_Registry::getInstance()->types->images->gif:
						case Zend_Registry::getInstance()->types->images->jpeg:
						case Zend_Registry::getInstance()->types->images->png:
							$this->view->file_name = $this->_helper->url->url(array('action'=>'index',
	    										  									'controller'=>'thumbs',
	    										  									'module'=>'default',
	    										  									'width'=>100,
																					'filename'=>$data[$this->_getFilenameFieldname()]),NULL,true);
							$this->view->file_url  = $this->_getUploadedFileUrl($id);
							break;
						default:
							$this->view->file_name = "/img/admin/".$this->view->GetFileIcon($data[$this->_getFilemimetypeFieldname()]);
							$this->view->file_url  = $this->_getUploadedFileUrl($id);
								
							
					}
					
					$this->view->file_title 	= $data[$this->_getFileorgnameFieldname()];
					$this->view->fieldname 		= $this->_getFilenameFieldname();
					$this->view->file_delete 	= $this->_helper->url->url(array('action'=>'deletefile',
    										  								 	 'controller'=>$this->_controller,
    										  								 	 'module'=>'admin',
    										  								 	 'id'=>$id),NULL,true);
					
					
				}
			}			
						
			$this->view->tableactions 		= $this->_getTableActions();
     		$this->view->controller   		= $this->_controller;
     		$this->view->tabs   	  		= $this->_getEditViewTabs($id);
     		$this->view->domain_displayname	= $this->_getDisplayName();
     		
     		$this->view->previous_id	    = $this->_getPreviousRecord($id);
   			$this->view->next_id	    	= $this->_getNextRecord($id);
   			
   			
     		
     		return $this->editRender();
		}
		else
			throw new Exception("TableConttroller::editAction(): id not set.");
    } 
    
    protected function _redirectAfterDelete(){
    	return $this->_helper->_redirector('index',$this->_controller,'admin',array('page'=>$page));
    }
    
    protected function checkDeleteAllowed($id){
    	
    	if ($this->_isAllowed('delete'))
    		return NULL;
    	else
    		return "U heeft geen verwijder rechten";   	
    }

	public function deleteAction()
    {
        $request   = $this->getRequest();
		$id        = $request->getParam('id',NULL);
		$confirmed = $request->getParam('confirm',false);
		$page	   = $request->getParam('page',1);
		
		if ($id !== NULL){
			
			$msg = $this->checkDeleteAllowed($id);
			
			if ($msg === NULL){		
				if ($confirmed){
					$this->_delete($id);
					return $this->_redirectAfterDelete();
					
				}
				else{
					$this->view->domain_displayname	= $this->_getDisplayName();
					$this->view->id 				= $id;
					$this->view->ctrl 				= strtolower($this->_domain_name);			
					$this->render('table/confirm',null,true);
				}
			}
			else{
				$this->view->reason             =  $msg;
				$this->view->controller    		=  $request->getParam('controller');	
				$this->view->domain_displayname =  $this->_domain_name;	
								
				$this->render('table/deletenotpossible',null,true);
			}
		}
		else
			throw new Exception("TableConttroller::deleteAction(): id not set.");
			
	}
	
	protected function bulkVerwijderen($ids)
	{
		$display_message = false;
		
		foreach($ids as $key=>$id){
			$msg = $this->checkDeleteAllowed($id);
			if ($msg !== NULL){
				unset($ids[$key]);
				$display_message = true;
			}
		}
		
		foreach($ids as $key=>$id)
			$this->_delete($id);
			
		if ($display_message){
			
			$this->view->reason             =  $msg;
			$this->view->controller    		=  $this->_controller;
			$this->view->domain_displayname =  $this->_domain_name;	
								
			return $this->render('table/deletenotpossible',null,true);			
		}			
		else
			return $this->_redirectAfterDelete();
	}
	
	public function dobulkAction()
    {
    	
       	$request   = $this->getRequest();
    	$ids       = $request->getParam('ids',		array());
    	$baction   = $request->getParam('bulkaction',NULL);
    	$confirmed = $request->getParam('confirmed',false);
    	$canceled  = $request->getParam('canceled',	false);
    	
    	$action       = $request->getParam('action',     NULL);
    	$controller   = $request->getParam('controller', NULL);
    	$order        = $request->getParam('order',  NULL);
	   	$sort    	  = $request->getParam('sort',   'asc');
	    $filterfield  = $request->getParam('filter', NULL);
	    $filtervalue  = $request->getParam('id',     NULL);
	    
	    $params = array('filter'=>$filterfield,'order'=>$order,'sort'=>$sort,'id'=>$filtervalue);    	
    	
    	if (!$canceled){
    		
    		
    		
	    	if ($confirmed){
	    		
	    		$methodName = 'bulk'.str_replace(' ','',$baction);
	    			    	
		    	if (($baction !== NULL) && (count($ids) > 0)){
		   			return $this->$methodName($ids);
		    	}
		    	
	    	}
	    	else{
	    		$this->view->ids = $ids;
	    		$this->view->bulkaction = $baction;
	    		$this->view->controller   = strtolower($this->_domain_name);
	    		$this->render('table/dobulk',NULL,true);
	    		return;    		
	    	}
    	}
    	
    	
    	return $this->_helper->getHelper('Redirector')->gotoSimple('index',$controller,'admin',$params);
    }
}