<?php 

class App_Model_Group extends App_Model_table{ 	
	
	public function fetchEntries($order="group_uniquename",$sort="asc",$filter=NULL){		
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();

		$select->from(array('g'=>'group'));
		
		$select->joinLeft(array('p'=>'group'),"g.group_parent_id = p.id",array('parent_name'=>'group_uniquename'));
		
		if ($filter !== NULL){
			
			$keys  = array_keys($filter);
			$field = $keys[0];
			$value = $filter[$field];
			
			$select->where("$field = ?",$value);
		}
		if ($order !== NULL)
			$select->order("$order $sort");
			
		$this->Log($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;		
	}
	
	public function fetchEntryByUniqueName($name)
    {
		if ($name === NULL)
			throw new Exception("Model_Group::fetchEntryByUniqueName : name parameter = NULL");
	
        $table = $this->getTable();
        $select = $table->select()->where('group_uniquename = ?', $name);
        		
		$rows = $table->fetchRow($select);
		if (NULL !== $rows)
		{
			return $rows->toArray();
		}
		else
			return array();
    }
	
	public function exists($uniquename)
    {
       		$db     = Zend_Registry::getInstance()->dbAdapter;
			$select = $db->select();

			$select->from(array('g'=>'group'));
			$select->where("group_uniquename = ?",$uniquename);
			
			$this->Log($select->assemble());			
			
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			if (count($rows) > 0)
				return true;
			else
				return false;
    }
    
	
	
	public function fetchDefault()
    {
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('g'=>'group'));
		$select->order('group_uniquename asc');
		$select->limit(1);
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
				
		return $rows[0];		
    }
    
	public function fetchChilds($group_id)
    {
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('g'=>'group'),array('id'));
		$select->where('group_parent_id = ?',$group_id);
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
				
		return $rows;		
    }
	
	
}