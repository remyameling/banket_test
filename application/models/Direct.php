<?php 

class App_Model_Direct extends App_Model_Table
{ 	
	
	function fetchWhereOrdernrIn($orders,$site_id,$order='ordernr',$sort='asc')
	{
		if (!is_array($orders))
			$orders = array(0=>$orders);		
		
		$orders = "('".implode("','",$orders)."')";
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
				
		$select->from(array('i'=>'direct'),array('ordernr','aantaldozen'=>'sum(aantaldozen)'));
		$select->where('site_id = ?',$site_id);
		$select->where("ordernr in $orders");
		$select->group('ordernr');
		$select->order("$order $sort");
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		
		return $rows;
	}
	
	function fetchLatestEndtime($ordernr,$site_id)
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
				
		$select->from(array('i'=>'direct'),array('eindtijd'=>'max(eindtijd)')); 
		$select->where('site_id = ?',$site_id);
		$select->where("ordernr = ?",$ordernr);
		
		$select->order("eindtijd desc");
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetch();
		
		return $rows;
	}
	
	function fetchByDate($range,$site_id,$lijn=NULL,$order='starttijd',$sort='desc')
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
				
		$select->from(array('d'=>'direct'));
		$select->where('site_id = ?',$site_id);
		$select->where('starttijd >= ?',$range['min']);
		$select->where('starttijd < ?' ,$range['max']);
		
		if ($lijn !== NULL)
			$select->where("lijnnr = ?",$lijn);
		
		$select->order("$order $sort");
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		
		return $rows;
	}
	
	public function fetchCountByOrdernum($site_id,$date,$order='ordernr',$sort='asc')
	{
		$db     	= Zend_Registry::getInstance()->dbAdapter;
		$select 	= $db->select();
		$ordernr	= $this->_getOrderNumber($date,"XX");
		$pos		= $this->_getorderNumberPosLinenum();
		$ordernr    = substr($ordernr,0,$pos);
				
		$select->from(array('d'=>'direct'),array('ORDERNR'=>'ordernr','omschrijving'=>'max(OMSCHRIJVING)','NUM_DOZEN'=>'SUM(aantaldozen)'));
		$select->where('site_id = ?',$site_id);
		$select->where("substr(ordernr,1,$pos) = ?",$ordernr);
		$select->group('ordernr');
		$select->order("$order $sort");
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		
		return $rows;
	}	
	
}