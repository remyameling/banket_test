<?php 

class App_Model_SortableTable extends App_Model_table{ 
	
    public function siwtchSortKeys($sortfield,$id1,$id2){
    	
    	$entry1 = $this->fetchEntry($id1);
    	$entry2 = $this->fetchEntry($id2);
    	$sortvalue1 = $entry1[$sortfield];
    	$sortvalue2 = $entry2[$sortfield];
    	
    	$entry_temp         	= $entry2;
    	$entry_temp[$sortfield] = -1;
    	
    	$entry1[$sortfield] = $sortvalue2;
    	$entry2[$sortfield] = $sortvalue1;
    	
    	$this->update($id2,$entry_temp);	// avoid intgrity constraint errors
    	$this->update($id1,$entry1);
    	$this->update($id2,$entry2);
    }
    
    private function findNextSortedItem($dir,$sort_field,$sort_value,$filter_field,$filter_value){
    	
    	if ($dir == 'up')
    		$sort = 'asc';
    	else
    		$sort = 'desc';
    	
    	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		
		$select->from(array('t'=>$this->_table_name));
		
		if ($dir == 'up')
			$select->where($sort_field.' > ?', $sort_value);
		else
			$select->where($sort_field.' < ?', $sort_value);
			
		$select->where($filter_field.' = ?',$filter_value);
		$select->order($sort_field.' '.$sort);
		$select->limit(1);
			
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		assert($rows !== NULL);
		assert(isset($rows[0]));
		assert(count($rows) == 1);
		return $rows[0];
    }
    
    public function editSort($id,$dir,$sort_field,$filter_field,$filter_value)
    {
    	$this_entry 	= $this->fetchEntry($id);
    	$other_entry	= $this->findNextSortedItem($dir,$sort_field,$this_entry[$sort_field],$filter_field,$filter_value);
    	
    	$this->siwtchSortKeys($sort_field,$this_entry['id'],$other_entry['id']);    	
    }
    
    public function nextSortValue($sort_field,$filter_field,$filter_value)
    {
    	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
				
		$select->from(array('t'=>$this->_table_name),array("max"=>"max($sort_field)"));
						
		$select->where($filter_field.' = ?',$filter_value);
		
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ($rows === NULL)
			return 0;
		else 	
			return $rows[0]['max']+1;
    }
    
	public function updateSortValues($sort_field,$sort_value,$filter_field,$filter_value)
    {
    	$table  = $this->getTable();
    	$data   = array($sort_field => new Zend_Db_Expr($sort_field.' - 1'));
    	
    	
    	$where = $table->getAdapter()->quoteInto("(($sort_field > ?) AND ($filter_field = $filter_value))",$sort_value,$filter_field,$filter_value);
    			
    	$table->update($data, $where); 
    }
    
	public function delete($id,$sort_field,$filter_field){
		
		assert($id !== NULL);
		assert($sort_field !== NULL);
		assert($filter_field !== NULL);
		
		$data 	 			= $this->fetchEntry($id);		
		$sortkey 			= $data[$sort_field];
		$filter_value 		= $data[$filter_field];	
		assert($filter_value !== NULL);
		
		$ret = parent::delete($id);
			
		$this->updateSortValues($sort_field,$sortkey,$filter_field,$filter_value);
		return $ret;		
	}
	
	public function fetchPrevious($id,$where,$sort_field,$sort_value,$where_active=NULL){
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = array('key'=>"MAX(".$sort_field.")");
		
		$select->from(array('z'=>$this->_getDBTableName()),$fields);
		$select->where($sort_field.' < ?',$sort_value);
		
		if ($where)
			$select->where($where);
		if ($where_active)
			$select->where($where_active);
		
		//die($select->assemble());
		$this->Log($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ((NULL !== $rows) && (isset($rows[0]['key'])))
		{
			$select = $db->select();
			$select->from(array('z'=>$this->_getDBTableName()),array('id'));
			$select->where($sort_field.' = ?',$rows[0]);
			
			if ($where)
				$select->where($where);
			
			$this->Log($select->assemble());
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			return $rows[0]['id'];			
		}
		else
			return NULL;
		
	}
	
	public function fetchNext($id,$where,$sort_field,$sort_value,$where_active=NULL){
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = array('key'=>"MIN(".$sort_field.")");
		
		$select->from(array('z'=>$this->_getDBTableName()),$fields);
		$select->where($sort_field.' > ?',$sort_value);
		
		if ($where)
			$select->where($where);
		if ($where_active)
			$select->where($where_active);
		
		$this->Log($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ((NULL !== $rows) && (isset($rows[0]['key'])))
		{
			$select = $db->select();
			$select->from(array('z'=>$this->_getDBTableName()),array('id'));
			$select->where($sort_field.' = ?',$rows[0]);
			
			if ($where)
				$select->where($where);
			
			$this->Log($select->assemble());
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			return $rows[0]['id'];			
		}
		else
			return NULL;
		
	}
}