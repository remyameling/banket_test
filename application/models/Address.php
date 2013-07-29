<?php 

class App_Model_Address extends App_Model_table{ 	
	
	public function fetchNumAddresses($member_id)
    {
		assert($member_id !== NULL);	
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('a'=>'address'),array('num'=>'count(*)'));
		$select->where('member_id = ?', $member_id);
		
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();

		if (NULL !== $rows)
		{
			return $rows[0]['num'];
		}
		else
			return array();	
    }
    
	public function fetchDefault($member_id){
    	
    	assert($member_id !== NULL);	
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('a'=>'address'));
		$select->where('member_id = ?', $member_id);
		$select->where('address_default = ?',1);
		
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();

		if (!empty($rows))
		{
			return $rows[0];
		}
		else
			return array();	    	
    }
    
	public function fetchByMember($member_id){
    	
    	assert($member_id !== NULL);	
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('a'=>'address'));
		$select->where('member_id = ?', $member_id);
		$select->order('address_default desc');
		
		$this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();

		if (!empty($rows))
		{
			return $rows;
		}
		else
			return array();	    	
    }
    
	public function resetDefault($member_id){
    	
    	$table  = $this->getTable();
    	
    	$where  = $table->getAdapter()->quoteInto('member_id = ?', $member_id);    	
    	$data   = array('address_default' => 0);
    	
    	$table->update($data, $where);    	
    }
    
	
	
}