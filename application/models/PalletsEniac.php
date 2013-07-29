 <?php 

class App_Model_PalletsEniac extends App_Model_Pallets
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
    		
    	return $config->db->tablename->pallets;
    }
    
	private function _getDBName($site_name)
    {
    	$config		= Zend_Registry::getInstance()->eniac->resources->get($site_name);
    	if ($config === NULL)
    		throw new Exception("ENIAC Config voor $site_name niet gevonden.");
    		
    	return $config->db->params->dbname;	
    }
	
    public function fetchMagazijnpallets($date,$site_name,$order,$sort)
	{
		$this->_Init($site_name);
		
		$dbname 	= $this->_getDBName($site_name);
		$tablename	= $this->_getTableName($site_name);
		$db 		= $this->connectEniacDB($site_name);
		
		$ordernr	= $this->_getOrderNumber($date,"XX");
		$pos        = $this->_getorderNumberPosLinenum();		
		$ordernr 	= substr($ordernr,0,$pos);
		
		$qry  = "SELECT VTORNR,VTALNR,AROMT1,QLMAGN  ,max( concat(concat(concat(concat(concat(PADONE,PADONY),'-'),PADONM),'-'),PADOND)) as ONTSTAAN,count(VTALNR) as NUM_PALLETS,SUM(VTQANT) AS NUM_DOZEN FROM ";
		$qry .= "$dbname.$tablename pal where SUBSTR(QLMAGN  ,2,1) = '.' AND substr(VTORNR,1,$pos) = '$ordernr' ";
		$qry .= "group by VTORNR,VTALNR,AROMT1,QLMAGN   order by $order $sort";
		
		
		$res = $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchAll: ".$qry);		
		}
		
		$data = $res->fetchall(PDO::FETCH_ASSOC);
		
		return $data;		
	}
	
	public function fetchAll($date,$site_name,$order,$sort)
	{
		$this->_Init($site_name);
		
		$dbname 	= $this->_getDBName($site_name);
		$tablename	= $this->_getTableName($site_name);
		$db 		= $this->connectEniacDB($site_name);
		
		$ordernr	= $this->_getOrderNumber($date,"XX");
		$pos        = $this->_getorderNumberPosLinenum();		
		$ordernr 	= substr($ordernr,0,$pos);
		
		$qry  = "SELECT * FROM ";
		$qry .= "$dbname.$tablename pal where substr(VTORNR,1,$pos) = '$ordernr' ";
		$qry .= "order by $order $sort";
		
		$res = $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchAll: ".$qry);		
		}
		
		$data = $res->fetchall(PDO::FETCH_ASSOC);
		
		return $data;		
	}
	
	public function fetchGroupedByArtikel($date,$site_name,$order,$sort)
	{
		$this->_Init($site_name);
		
		$dbname 	= $this->_getDBName($site_name);
		$tablename	= $this->_getTableName($site_name);
		$db 		= $this->connectEniacDB($site_name);
		$ordernr	= $this->_getOrderNumber($date,"XX");
		$pos        = $this->_getorderNumberPosLinenum();		
		$ordernr 	= substr($ordernr,0,$pos);
		
		$qry  = "SELECT VTALNR,COUNT(VTALNR) AS NUM_PALLETS,MAX(AROMT1) as AROMT1,MAX(VTTIME) AS VTTIME, max(concat(concat(concat(PADTHD,'-'),concat(PADTHM,'-')),concat(PADTHE,PADTHY))) AS THT,SUM(VTQANT) as VTQANT,SUM(ARNGKG*VTQANT) AS GEWICHT FROM ";
		$qry .= "$dbname.$tablename pal where substr(VTORNR,1,$pos) = '$ordernr' ";
		$qry .= "group by VTALNR  ";
		$qry .= "order by $order $sort";
		
		$res = $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchAll: ".$qry);		
		}
		
		$data = $res->fetchall(PDO::FETCH_ASSOC);
		
		return $data;		
	}
	
	public function fetchByArtikel($date,$site_name,$vtalnr,$order,$sort)
	{
		$this->_Init($site_name);
		
		$dbname 	= $this->_getDBName($site_name);
		$tablename	= $this->_getTableName($site_name);
		$db 		= $this->connectEniacDB($site_name);		
		$ordernr 	= $this->_getOrderNumber($date,"XX");
		$pos        = $this->_getorderNumberPosLinenum();	
		$ordernr 	= substr($ordernr,0,$pos);
		
		$qry  = "SELECT VTALNR,AROMT1,VTTIME,VTSSCC,concat(concat(concat(PADTHD,'-'),concat(PADTHM,'-')),concat(PADTHE,PADTHY)) AS THT,VTQANT as VTQANT,ARNGKG*VTQANT AS GEWICHT FROM ";
		$qry .= "$dbname.$tablename pal where substr(VTORNR,1,$pos) = '$ordernr' AND VTALNR = '$vtalnr' ";
		$qry .= "order by $order $sort";
		
		$res = $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchAll: ".$qry);		
		}
		
		$data = $res->fetchall(PDO::FETCH_ASSOC);
		
		return $data;		
	}
	
	public function fetchByOrderAndCode($site_name,$artikelnr,$ordernr,$magazijncode,$order,$sort)
	{
		$this->_Init($site_name);
		
		$dbname 	= $this->_getDBName($site_name);
		$tablename	= $this->_getTableName($site_name);
		$db 		= $this->connectEniacDB($site_name);
		
		$qry  = "SELECT VTALNR,AROMT1,VTORNR,VTORRL,VTTIME,VTQANT,VTSSCC,QLMAGN  ,concat(concat(concat(concat(concat(PADONE,PADONY),'-'),PADONM),'-'),PADOND) as ONTSTAAN FROM ";
		$qry .= "$dbname.$tablename pal where QLMAGN = '$magazijncode' AND VTORNR = '$ordernr' AND VTALNR = '$artikelnr' ";
		
		$res = $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchAll: ".$qry);		
		}
		
		$data = $res->fetchall(PDO::FETCH_ASSOC);
		
		return $data;		
	}
	
	public function fetchCountByOrdernum($site_name,$date,$order='ORDERNR',$sort='ASC')
	{
		$this->_Init($site_name);
		
		$dbname 	= $this->_getDBName($site_name);
		$tablename	= $this->_getTableName($site_name);
		$db 		= $this->connectEniacDB($site_name);
		$ordernr	= $this->_getOrderNumber($date,"XX");
		$pos        = $this->_getorderNumberPosLinenum();
		$ordernr    = substr($ordernr,0,$pos);
		
		$qry  = "SELECT concat(VTORNR,VTORRL) as ORDERNR,MAX(AROMT1) as OMSCHRIJVING,SUM(VTQANT) as NUM_DOZEN FROM ";
		$qry .= "$dbname.$tablename pal where substr(VTORNR,1,$pos) = '$ordernr' ";
		$qry .= "GROUP BY concat(VTORNR,VTORRL) ";
		$qry .= "order by $order $sort";
		
		$res = $db->query($qry);	
		
		if (!$res)
		{
			print_r($db->errorInfo());
			die("fetchAll: ".$qry);		
		}
		
		$data = $res->fetchall(PDO::FETCH_ASSOC);
		
	 	return $data;		
	}
}

?>