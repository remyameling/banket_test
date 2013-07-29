<?php 

class App_Model_Setting extends App_Model_Table
{ 	
	public function exists($uniquename)
    {
       		$db     = Zend_Registry::getInstance()->dbAdapter;
			$select = $db->select();

			$select->from(array('s'=>'setting'));
			$select->where("setting_name = ?",$uniquename);
			
			$this->Log($select->assemble());			
			
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			if (count($rows) > 0)
				return true;
			else
				return false;
    }
    
    
	
}