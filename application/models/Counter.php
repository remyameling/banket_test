<?php 

class App_Model_Counter extends App_Model_table
{ 	
	public function increment($counter_name)
	{
		// increment value
		
    	$table  = $this->getTable();
    	$data   = array('counter_value' => new Zend_Db_Expr('counter_value + 1'));
    	
    	$where = $table->getAdapter()->quoteInto("counter_name = ?",$counter_name);
    			
    	$table->update($data, $where); 
    	
    	// get value
    	
    	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('c'=>'counter'),array('counter_value'));
		$select->where('counter_name = ?', $counter_name);
		
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		assert(isset($rows[0]));

		return $rows[0]['counter_value'];	
    }
    
	public function fetchnext($counter_name)
	{
		// get value
    	
    	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('c'=>'counter'),array('counter_value'));
		$select->where('counter_name = ?', $counter_name);
		
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		assert(isset($rows[0]));

		return $rows[0]['counter_value']+1;	
    }
}