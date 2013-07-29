<?php

require_once "GroupedTableController.php";

class Admin_UserController extends Admin_GroupedTableController
{
	protected function _getTableColumnNames(){
		return array('username'=>'user_name','alias'=>'user_alias','email'=>'user_email','laatste login'=>'user_lastlogin','aantal logins'=>'user_numlogins','actief'=>'user_active','groep'=>'group_name');	
	}
	
	protected function _getTableColumnSorts(){
		return array('username'=>'user_name','alias'=>'user_alias','email'=>'user_email','laatste login'=>'user_lastlogin','aantal logins'=>'user_numlogins','actief'=>'user_active','groep'=>'group_name');	
	}
	
	protected function _getTableAlphaFields(){
		return array('user_name','user_alias','user_email');	
	}
	
	protected function _getTableColumnDecoder(){
		return array('user_active'=>array(0=>'nee',1=>'ja'));
	}
	
	protected function _getBulkActions(){
		
		$actions = parent::_getBulkActions();
		if ($this->_isAllowed('edit')){
			$actions[]   = "Activeren";
			$actions[]   = "Deactiveren";
		}
    		
		return $actions;
	}

	protected function _getJoins(){
		return array( array('table'=>'group','field'=>'group_id','joinfields'=>array('group_name'=>'group_uniquename')));
	}
	
	protected function _getAccociatedTables(){
		return NULL;
	}
	
	protected function _getGroupModel(){
		if ($this->_groupedModel == NULL)
			$this->_groupedModel = new App_Model_Group();
			
		return $this->_groupedModel;
	}
	
	protected function _getGroupControllerName(){
		return "group";
	}	
	
	protected function _getGroupControllerDisplayName(){
		return "groep";
	}
	
	protected function _getGroupTableLabelField(){
		return "group_uniquename";
	}
	
	protected function _getGroupByField(){
		return "group_id";
	}
	
	protected function _fetchGroupValues(){
		
		$data  = parent::_fetchGroupValues();
		
		// remove 'root' group, if role of current user is not root
		if (!$this->_hasRootPermission()){
		
			foreach($data as $index=>$group){
				if ($group['group_uniquename'] == Zend_Registry::getInstance()->roles->roles->root->name)
					unset($data[$index]);
			}		
		}
		
		return $data;
	}
	
	protected function _getEditForm()
	{
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		
		$form = $this->_getForm("edit");
		
		if ((!$this->_hasRootPermission()) && ($id != $this->_getCurrentUserId()))	// if no root permissions and 
		{																			// user id to edit is not own user id
			$form->removeElement('user_password');			
		}
		if (!$this->_hasRootPermission())											// if no root permissions 
		{																			// remove value for root 
																					// group from group_id field
																					
			// prevent from giving user root privileges
			
			$options = $form->getElement('group_id')->getMultiOptions();
			
			foreach($options as $key=>$value){
				if ($value == Zend_Registry::getInstance()->roles->roles->root->name)
					unset($options[$key]);
			}
			
			$form->getElement('group_id')->clearMultiOptions();
			$form->getElement('group_id')->addMultiOptions($options);
		}
		return $form;
	}
	
	
	public function editAction()
    {
    	$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		
		// check if role of user to edit is 'root'
		
		$user_group_data = $this->_getModel()->fetchRoleByUserId($id);
		if ($user_group_data['role'] == Zend_Registry::getInstance()->roles->roles->root->name)
		{
			// user to edit has role 'root', check if current user has root permissions
			if (!$this->_hasRootPermission())
				die("niet toegestaan");
		}

		return parent::editAction();
		
	} 
		
	protected function checkDeleteAllowed($id)
	{
		$data = $this->_getModel()->fetchEntry($id);
		assert($data !== NULL);
		
		// check if system user
		if ($data['user_system'] == 1)
			return "Deze gebruiker is een systeem gebruiker.";
			
		// check op verwijderen van zichzelf
		
		if ($id == $this->_getCurrentUserId())
			return "Verwijderen van huidige gebruiker is niet mogelijk";
			
		return NULL;
	}
	
	protected function _initAddForm(){
    	return array('user_password'=>$this->createHash(6));
    }
	
	protected function bulkActiveren($ids)
	{
		$data['user_active'] = 1;
		foreach($ids as $key=>$id){
			
			$this->_update($id,$data);
		}
			
		return $this->_helper->redirector('index',$this->_controller);
	}
	protected function bulkDeactiveren($ids)
	{
		$data['user_active'] = 0;
		foreach($ids as $key=>$id){
			
			$this->_update($id,$data);
		}
			
		return $this->_helper->redirector('index',$this->_controller);
	}
	
}