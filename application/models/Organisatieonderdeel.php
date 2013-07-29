<?php 

class App_Model_Organisatieonderdeel extends App_Model_table
{ 	
		
	public function fetchEntryEx($id)
    {
    	assert($id != NULL);
	
        $db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();

		$table  = $this->getTable();
		$fields = $table->getFields();
		
		$select->from(array('o'=>'organisatieonderdeel'),$fields);
		$select->join(array('a'=>'address'),"a.id = o.address_id");
		$select->join(array('c'=>'country'),"c.id = a.address_country");
		$select->where('o.id = ?', $id);
        
        $this->Log($select->assemble());
			
		$stmt = $db->query($select);
		$rows = $stmt->fetch();

		return $rows;   	
    }
}