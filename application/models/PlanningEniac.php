<?php 

class App_Model_PlanningEniac extends App_Model_Planning
{
	private function connectEniacDB($site_name)
    {
    	$config		= Zend_Registry::getInstance()->eniac->resources->get($site_name);
    	if ($config === NULL)
    		throw new Exception("ENIAC Config voor $site_name niet gevonden.");
    	
    	$server     = $config->db->params->host;
    	$user       = $config->db->params->username;
    	$pass       = $config->db->params->password;	
		
		return new PDO($server, $user, $pass);	
    }
    
    private function _getTableName($site_name)
    {
    	$config		= Zend_Registry::getInstance()->eniac->resources->get($site_name);
    	if ($config === NULL)
    		throw new Exception("ENIAC Config voor $site_name niet gevonden.");
    		
    	return $config->db->tablename->planning;
    }
    
	private function _getPalletsTableName($site_name)
    {
    	$config		= Zend_Registry::getInstance()->eniac->resources->get($site_name);
    	if ($config === NULL)
    		throw new Exception("ENIAC Config voor $site_name niet gevonden.");
    		
    	return $config->db->tablename->pallets;
    }
    
	protected function _getTimeShift($site_name)
    {
    	$config		= Zend_Registry::getInstance()->sites->dienst->get($site_name);
    	if ($config === NULL)
    		throw new Exception("sites Config voor $site_name niet gevonden.");
    		
    	$start = $config->start->get(1)->uur;
    	
    	return $start*100000;	/* tijd in hhmmss, verschuiven i.v.m. bepalen max */
    }
    
    private function _getDBName($site_name)
    {
    	$config		= Zend_Registry::getInstance()->eniac->resources->get($site_name);
    	if ($config === NULL)
    		throw new Exception("ENIAC Config voor $site_name niet gevonden.");
    		
    	return $config->db->params->dbname;	
    }
	
    public function fetchPlannedOrders($lijn,$date,$site_name)
	{
		$this->_Init($site_name);
		
		$dbname 			= $this->_getDBName($site_name);
		$plantablename 		= $this->_getTableName($site_name);
		$pallettablename    = $this->_getPalletsTableName($site_name);
		$db 				= $this->connectEniacDB($site_name);		
		$ordernr 			= $this->_getOrderNumber($date,$lijn);
		
		$timeshift			= $this->_getTimeShift($site_name);
		
		//$ordernr = 'P1224203';
		
		// geplande orders met aantal pallets voor lijn en datum, gesorteerd op regelnr (num_pallets = aantal gepland)
		
		
		$qry = "SELECT 
				PDORNR,
				PDORRL,
				MAX(PDALNR) AS PDALNR,
				MAX(PDOMAL) AS PDOMAL,
				MAX(PDQTTP) AS NUM_DOZEN,
				max(VTTIME-$timeshift)+$timeshift as MAX_TIME,
				max(P2QAN1) as NUM_DOZEN_PER_PALLET,
				COUNT(DISTINCT(pallet.VTSSCC)) AS NUM_PALLETS_GEREEDGEMELD
			    FROM $dbname.$plantablename pl
				LEFT OUTER JOIN $dbname.$pallettablename pallet on pl.PDORNR = pallet.VTORNR AND pl.PDORRL = pallet.VTORRL
			    WHERE PDORNR = '$ordernr' GROUP BY PDORNR,PDORRL ORDER BY PDORRL ASC";
				
		//$qry = "SELECT * FROM $dbname.$plantablename pl WHERE SUBSTR(PDORNR,1,1) = 'N'";
				
		$res 		= $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchByLineAndDate: ".$qry);		
		}
		
		$data 		= $res->fetchall(PDO::FETCH_ASSOC);
		
		
		//print_r($data);
		//die($qry);		
		
		
		return $data;		
	}
	
	public function fetchPlannedOrder($ordernr,$orderrl,$site_name)
	{
		$this->_Init($site_name);
		
		$dbname 			= $this->_getDBName($site_name);
		$plantablename 		= $this->_getTableName($site_name);
		$pallettablename    = $this->_getPalletsTableName($site_name);
		$db 				= $this->connectEniacDB($site_name);		
				
		$timeshift			= $this->_getTimeShift($site_name);
		
		//$ordernr = 'P1224203';
		
		// geplande orders met aantal pallets voor lijn en datum, gesorteerd op regelnr (num_pallets = aantal gepland)
		
				
		$qry = "SELECT 
				max(PDOMAL) as PDOMAL,
				max(PDALNR) as PDALNR,
			    max(PDORNR) as PDORNR,
			    max(PDORRL) AS PDORRL,
			    max(PDQTTP) as NUM_DOZEN,
			    max(P2QAN1) as NUM_DOZEN_PER_PALLET,
			    max(VTTIME-$timeshift)+$timeshift as MAX_TIME,
			    COUNT(DISTINCT(VTSSCC)) AS NUM_PALLETS_GEREEDGEMELD 
			    FROM $dbname.$plantablename pl 
			    LEFT OUTER JOIN $dbname.$pallettablename pallet on pl.PDORNR = pallet.VTORNR AND pl.PDORRL = pallet.VTORRL
				WHERE PDORNR = '$ordernr' AND PDORRL = '$orderrl'";
				
		//$qry = "SELECT count(VTORNR) as num FROM $dbname.$pallettablename where VTORNR = '$ordernr' AND VTORRL = '$orderrl'";
		
		//die($qry);
				
		$res 		= $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchByLineAndDate: ".$qry);		
		}
		
		$data 		= $res->fetch(PDO::FETCH_ASSOC);
		
		//echo "$qry<hr /><pre>";
		//print_r($data);
		//die();
		
		return $data;		
	}
	
	public function fetchLatestReady($ordernr,$orderrl,$site_name)
	{
		$this->_Init($site_name);
		
		$dbname 			= $this->_getDBName($site_name);
		$plantablename 		= $this->_getTableName($site_name);
		$pallettablename    = $this->_getPalletsTableName($site_name);
		$db 				= $this->connectEniacDB($site_name);		
				
		$timeshift			= $this->_getTimeShift($site_name);
		
		$qry = "SELECT 
				max(VTTIME-$timeshift)+$timeshift as MAX_TIME,
			    COUNT(DISTINCT(VTSSCC)) AS NUM_PALLETS_GEREEDGEMELD 
			    FROM $dbname.$plantablename pl 
			    LEFT OUTER JOIN $dbname.$pallettablename pallet on pl.PDORNR = pallet.VTORNR AND pl.PDORRL = pallet.VTORRL
				WHERE PDORNR = '$ordernr' AND PDORRL = '$orderrl'";
				
		//die($qry);
				
		$res 		= $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchByLineAndDate: ".$qry);		
		}
		
		$data 		= $res->fetch(PDO::FETCH_ASSOC);
		
		//echo "$qry<hr /><pre>";
		//print_r($data);
		//die();
		
		return $data;		
	}
	
	public function fetchCurrentOrder($lijn,$date,$site_name)
	{
		$this->_Init($site_name);
		
		$dbname 			= $this->_getDBName($site_name);
		$plantablename 		= $this->_getTableName($site_name);
		$pallettablename    = $this->_getPalletsTableName($site_name);
		$db 				= $this->connectEniacDB($site_name);		
				
		$ordernr 			= $this->_getOrderNumber($date,$lijn);
		
		$qry = "SELECT MAX(VTORRL) AS VTORRL FROM $dbname.$pallettablename pl WHERE VTORNR = '$ordernr'";
				
		//die($qry);
				
		$res 		= $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchByLineAndDate: ".$qry);		
		}
		
		$data = $res->fetch(PDO::FETCH_ASSOC);
		if (isset($data['VTORRL']))
			$data['VTORNR'] = $ordernr;
		
		return $data;
	}
}

?>