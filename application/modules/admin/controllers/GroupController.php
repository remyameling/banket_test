<?php

require_once "TableController.php";

class Admin_GroupController extends Admin_TableController
{
	protected function _getTableColumnNames(){
		return array('Unieke naam'=>'group_uniquename','Erft van'=>'parent_name');	
	}
	
	protected function _getTableColumnSorts(){
		return array('Unieke naam'=>'group_uniquename','Erft van'=>'parent_name');	
	}
	
	protected function _getTableColumnDecoder(){
		return array('group_system'=>array(0=>'nee',1=>'ja'));
	}

	protected function _getJoins(){
		return NULL;
	}
	
	protected function _getAccociatedTables(){
		return array(array('title'=>'Toon alle users in deze groep',
						   'controller'=>'user',
						   'action'=>'index',
						   'params'=>array('ff'=>'group_id'),
						   'id_param'=>'fv',
						   'id_field'=>'id',						   		
						   'icon'=>'user_small.png'));
	}
	
	protected function _getTableFilterValues(){
		return NULL;
	}
	
	public function editAction()
    {
    	$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		$data    = $this->_getModel()->fetchEntry($id);
		
		if ($data['group_system'] == 1)
			return $this->render('notallowed');
		else
			parent::editAction();
    }
	
	protected function checkDeleteAllowed($id)
	{
		$group = $this->_getEditData($id);
		if ($group['group_system'] == 0)
			return NULL;
		else
			return "Deze groep heeft speciale betekenis.";
	}
	
	protected function _update($id,$data)
	{
		unset($data['group_uniquename']);	
						
		return parent::_update($id,$data);
	}
}