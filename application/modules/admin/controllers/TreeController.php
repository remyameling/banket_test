<?php

require_once "BaseController.php";

abstract class Admin_TreeController extends Admin_BaseController 
{
	protected $_model 		= NULL;
	protected $_domain_name = NULL;
	protected $_controller  = NULL;
	protected $_model_class	= NULL;
	
	protected $_logGroup 	= "ADMIN";
	
	const TREE_DIV_ID  		= "tree";
	const ROOT_NODE_ID 		= "root";
	
	abstract protected function _newItem($label,array $data);
	abstract protected function _getRootLabel($container_id);
	abstract protected function _getContainerDomainname();
	abstract protected function _getContainerNameField();
	
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
			
		parent::__construct($request,$response,$invokeArgs);		
	}
	
	public function init()
	{
		$ret = parent::init();
		$this->_helper->layout->disableLayout();
		return $ret;
		
    }
	
	protected function _getEditForm(){
		return $this->_getForm("edit");
	}
	
	protected function _getModel()
    {
        if (null === $this->_model){
			$this->_model = new $this->_model_class();
        }
        return $this->_model;
    }
    
	protected function _getDisplayName(){

		if (Zend_Registry::getInstance()->admin_const->controller->get($this->_controller) === NULL)
			throw new Exception("TableController::_getDisplayName(): domaindisplayname voor controller ".$this->_controller." niet gevonden in admin const.ini");
		
		return Zend_Registry::getInstance()->admin_const->controller->get($this->_controller)->domaindisplayname;
		
		return $this->_domain_name;
	}
	
	protected function _getContainerDisplayName(){
		
		$controller_name       = strtolower($this->_getContainerDomainname());

		if (Zend_Registry::getInstance()->admin_const->controller->get($controller_name) === NULL)
			throw new Exception("TreeController::_getContainerDisplayName(): domaindisplayname voor controller ".$this->_controller." niet gevonden in admin const.ini");
		
		return Zend_Registry::getInstance()->admin_const->controller->get($controller_name)->domaindisplayname;		
	}
	
	protected function _getContainerModel()
    {
    	$container_model_class = "App_Model_".$this->_getContainerDomainname();
    	return new $container_model_class();
    }
    
	protected function _getDefaultContainerId()
    {
    	$data = $this->_getContainerModel()->fetchDefault();
    	return $data['id'];
    }
    
	protected function _isAllowed($actionname)
	{
       	$role 		= App_Auth_Auth::getInstance()->getRole();
    	$resource 	= "admin_".$this->_controller;
    	return Zend_Registry::getInstance()->acl->isAllowed($role,$resource,$actionname);
    }
    
	protected function checkDeleteAllowed($id)
	{
    	if ($this->_isAllowed('delete'))
    	{
    		return NULL;
    	}    		
    	else
    		return "U heeft geen verwijder rechten";   	
    }
    
	public function indexAction() 
    {
    	$request   		= $this->getRequest();
	    $container_id   = $request->getParam('id',$this->_getDefaultContainerId());
	    $init_open 		= $request->getParam('init_open',NULL);
	    
	    $this->Log("container_id=$container_id");	    
	    
	    $this->initLayout(Zend_Registry::getInstance()->paths->layout->admin->default,
						  APPLICATION_PATH.'/layouts/admin/scripts/');	
	    
		$model 						= $this->_getModel();
		$containerMdl               = $this->_getContainerModel();
	    $data                       = $containerMdl->fetchEntry($container_id);
	    $entries					= $containerMdl->fetchEntries();	
						  
	    $this->view->items     				= $model->fetchItems($container_id);
	    $this->view->labelfield 			= $this->_getModel()->getLabelField();
	    $this->view->init_open 				= $init_open;
	    $this->view->controller				= $this->_controller;
	    $this->view->id						= $container_id;
	    $this->view->rootname				= $this->_getRootLabel($container_id);	    
	    $this->view->items_objectname		= $this->_getDisplayName();
	    $this->view->container_objectname	= $this->_getContainerDisplayName();        	    
	    $this->view->container_name			= $data[$this->_getContainerNameField()];
	    $this->view->container_name_field	= $this->_getContainerNameField();
	    $this->view->container_entries      = $entries;	
	    $this->view->tree_div_id			= self::TREE_DIV_ID;
	    $this->view->root_node_id			= self::ROOT_NODE_ID;
	    	    
	    return $this->render('tree/index',null,true);
    }
    
	protected function _getEditFormAction($id){
    	return $this->_helper->url->direct('edit',null,null,array('id'=>$id));
    }
    
	protected function _getEditData($id){		
		return $this->_getModel()->fetchEntry($id);
	}
    
	public function setfocusAction() 
    {
    	$request    = $this->getRequest();
    	$id  		= $request->getParam('id',NULL);
    	$data       = NULL;
    	
    	$this->Log("set focus node. id:$id");
    	
    	if ($id !== NULL){
    		    		
    		$data        = $this->_getEditData($id);
    		assert($data !== NULL);
    		
    		$form        = $this->_getEditForm();
    		
    		$form->setDefaults($data);
    	}
    	
    	$this->view->id 	= $id;
	    $this->view->form 	= $form;
	    
	    return $this->render('tree/setfocus',null,true);
    }
    
	protected function _redirectAfterEditToEdit($id){
    	return $this->_helper->redirector('edit',$this->_controller,'admin',array('id'=>$id));
    }
    
	protected function _update($id,$data)
	{
		unset($data['frmid']);
		
		$model = $this->_getModel();
		return $model->update($id,$data);
	}
    
	protected function _handleEditForm($form){
		
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		$data    = $form->getValues();
		unset($data['save']);
		unset($data['back']);
		
    	return $this->_update($id,$data);   	
    }
    
    public function editAction()
    {
    	$this->_helper->layout->setLayout('content_only');
    	$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		
		if ($id !== NULL){
			
			$ret = $this->_handleForm($this->_getEditForm(),
    								  $this->_getEditData($id),
    								  "_handleEditForm",
    								  $this->_getEditFormAction($id));
    								  
			if ($ret == self::FORM_HANDLED)
			{
				$data              = $this->_getEditData($id);
				$this->view->label = $data[$this->_getModel()->getLabelField()];
				
				$this->_form	   = NULL;
				$this->view->form  = $this->_getEditForm();
				$this->view->form->setDefaults($this->_getEditData($id));						
			}
			else
				$this->view->label = "";
			
     		return $this->render('tree/edit',NULL,true);
		}
		else
			throw new Exception("TreeConttroller::editAction(): id not set.");
    } 
    
	public function renamenodeAction() 
    {
    	$request = $this->getRequest();
    	$id      = $request->getParam('id',NULL);
    	$label   = $request->getParam('label',NULL);
    	
    	$this->Log("id=$id,label=$label");
    	
    	$model	 = $this->_getModel();
    	$data    = $model->fetchEntry($id);
    	
    	$ndata[$model->getLabelField()] = $label;
    	
    	$model->update($id,$ndata);
    	
    	return $this->render('tree/renamenode',null,true);
    }
    
	public function createnodeAction() 
    {
    	$request    	= $this->getRequest();
    	$parent_id  	= $request->getParam('parent',NULL);
    	$container_id  	= $request->getParam('container',NULL);
    	$label	    	= $request->getParam('label',"Nieuw");
    	
    	$this->Log("parent_id=$parent_id,label=$label,container=$container_id");
    	
    	if ($parent_id == self::ROOT_NODE_ID)
    		$parent_id = 0;
    	
    	$model	    = $this->_getModel();
    	
    	$data                                = $this->_newItem($label,array());
    	$data[$model->getContainerIdField()] = $container_id;
    	$data[$model->getLabelField()]	     = $label;
    	
    	$id = $model->save($data,$parent_id);   	
    	
	    $this->view->id = $id; 
	    
	    return $this->render('tree/createnode',null,true);
    }
    
    protected function delete($id)
    {
    	$model 	= $this->_getModel();
    	$model->delete($id);
    }
    
	protected function deletenodeAction() 
    {
    	$request    = $this->getRequest();
    	$id  		= $request->getParam('id',NULL);
    	
    	$msg 		= $this->checkDeleteAllowed($id);
    	if ($msg === NULL)
    	{	
			if ($id !== NULL){
    			$this->delete($id);
    		}
    			
	    	$this->view->id = $id;		

	    	return $this->render('tree/deletenode',null,true);
    	}
    	else
    	{
    		$this->view->msg = $msg;
    		return $this->render('tree/notallowed',null,true);
    	}
    }
    
	public function movenodeAction() 
    {
    	
    	$request    = $this->getRequest();
    	$id  		= $request->getParam('id',NULL);
    	$parent		= $request->getParam('ref',NULL);
    	$position	= $request->getParam('position',NULL);
    	
    	$this->Log("id:$id, parent: $parent, pos:$position");
    	
    	if ($parent == self::ROOT_NODE_ID)
    		$parent = 0;    	
    	
    	if ($id !== NULL){
    		
    		$position++; // jsTree is 0-based, sort keys are 1-based.
    		
    		$model		 = $this->_getModel();
    		// get current data
    		$data        = $model->fetchEntry($id);
    		    		
    		// update sortkeys
    		$this->Log("model::removeSortValue(".$data[$model->getSortField()].",".$data[$model->getParentField()].")");
    		
    		$model->removeSortValue($data[$model->getSortField()],
    							    $data[$model->getParentField()],
    							    $model->getContainerIdField(),
    							    $data[$model->getContainerIdField()]);
    		
    		// insert sortkey
    		
		    $this->Log("model::insertSortValue($position,$parent)");
    		$model->insertSortValue($position,$parent,$model->getContainerIdField(),$data[$model->getContainerIdField()]);
    		
    		// update sortkey en parent
    		
    		$data[$model->getSortField()]   = $position;
    		$data[$model->getParentField()] = $parent;
    		$model->update($id,$data);
    	
    	}
    	
	    return $this->render('tree/movenode',null,true);
    }
    
	
}