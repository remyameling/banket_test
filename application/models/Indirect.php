<?php 

class App_Model_Indirect extends App_Model_Table
{ 	
	
	function fetchByDateAndDienst($date,$dienst_id,$site_id)
	{
		$date   = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('i'=>'indirect'));
		$select->where('site_id = ?',	$site_id);
		$select->where('datum = ?',		$date);
		$select->where('dienst_id = ?' ,$dienst_id);
		
		//die($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
	}
	
	function fetchByDate($range,$site_id,$order='datum',$sort='desc')
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		
				
		$select->from(array('i'=>'indirect'));
		$select->where('site_id = ?',$site_id);
		$select->where('datum >= ?',substr($range['min'],0,10));
		$select->where('datum < ?' ,substr($range['max'],0,10));
		
		$select->order("$order $sort");
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
	}
	
	
}