<?php

class App_Model_Table
{
    protected $_table			= NULL;
	protected $_table_name		= NULL;
	protected $_domain_name 	= NULL;
	protected $_currentSiteID 	= NULL;
	protected $_logGroup 		= "SQL";
	
	public function getTableName(){
		return $this->_table_name;
	}
	
	protected function _getCurrentSiteId()
	{
		if ($this->_currentSiteID === NULL){
		
			$data = App_ModelFactory::getModel("user")->fetchEntry(App_Auth_Auth::getInstance()->getIdentityId());
	    	if (isset($data['user_site_id']))
	    		$this->_currentSiteID = $data['user_site_id'];
	    	else
	    		$this->_currentSiteID = NULL;
		}
		
		return $this->_currentSiteID;
    }
	
	
	protected function _getOldOrderNumber($date,$lijn)
    {
    	$datet  = mktime(0,0,0,substr($date,3,2),substr($date,0,2),substr($date,6,4));
    	
    	$year   = substr($date,8,2);
		$week   = date("W",$datet);
		$day    = date("N",$datet);
		$line   = "0$lijn";
		
		//echo "year=$year; week=$week; day=$day; line=$line<br />";
		
		return "P$year$week$day$line";	
    }
    
    protected function _getorderNumberPosLinenum()
    {
    	// retourneert de positie (0-based) van 'lijn nummer' in ordernummer, afhankelijk
    	// van Zend_Registry::getInstance()->consts->ordernummercodering->methode
    	
    	if (Zend_Registry::getInstance()->consts->ordernummercodering->methode == "oud")
    		return 6;
    	else
    		return 7;
    }
    
	
	protected function _getOrderNumber($date,$lijn)
    {
    	if (Zend_Registry::getInstance()->consts->ordernummercodering->methode == "oud")
    		return $this->_getOldOrderNumber($date,$lijn);
    		
    	$datet   = mktime(0,0,0,substr($date,3,2),substr($date,0,2),substr($date,6,4));
    	    	
    	$locatie = Zend_Registry::getInstance()->consts->ordernummercodering->locatie->get($this-> _getCurrentSiteId());
    	$type    = Zend_Registry::getInstance()->consts->ordernummercodering->type;
    	$year    = substr($date,9,1);
    	$month   = substr($date,3,2);
    	$day     = date("d",$datet);
    	$lijn    = substr($lijn,0,1);
    	
    	$ordernr = $locatie.$type.$year.$month.$day.$lijn;
    	
    	return $ordernr;    		
    }
	
	private function LogMsg($msg,$group,$prio){
    	
    	if (Zend_Registry::getInstance()->logging->groups->get($group) == 1){
    		
    		$function = "";
    		$class    = "";
    		
    		if (Zend_Registry::getInstance()->logging->logcaller){
    	
	    		$trace=debug_backtrace();
			
				$caller=array_shift($trace);
				$caller=array_shift($trace);
				$caller=array_shift($trace);
	
				$function = $caller['function'];
				if (isset($caller['class']))
					$class = $caller['class']."::";
    		}
				
			Zend_Registry::getInstance()->logger->log($group.":".$class.$function." ".$msg,$prio);
    	}    	
    }
    
	protected function Log($msg)			// Debug: debug messages
	{
		$this->LogMsg($msg,$this->_logGroup,Zend_Log::DEBUG);
	}
	
	protected function LogNotice($msg)		// Notice: normal but significant condition
	{
		$this->LogMsg($msg,$this->_logGroup,Zend_Log::NOTICE);
	}
	
	protected function LogError($msg)		// Error: error conditions
	{
		$this->LogMsg($msg,$this->_logGroup,Zend_Log::ERROR);
	}
	
	protected function LogAlert($msg)		// Alert: action must be taken immediately
	{
		$this->LogMsg($msg,$this->_logGroup,Zend_Log::ALERT);
	}
	
	protected function _getDBTableName(){
		$table = $this->getTable();
		return $table->get_table_name();
	}
	
	public function __construct($domain_name=NULL,$table_name=NULL){
		
		if ($domain_name == NULL)
		{
			$name  = get_class($this);
			$parts = explode("_",$name);
			$this->_domain_name = $parts[2];
		}
		else
			$this->_domain_name = $domain_name;
			
		if ($table_name == NULL)
			$this->_table_name  = strtolower($this->_domain_name);
		else
			$this->_table_name  = $table_name;		
	}
	
	public function getTable()
    {
    	
    	if (null === $this->_table)
		{			
			if ($this->_table_name === NULL){				
				throw new Exception('Tablename niet geinitialiseerd voor model ');				
			}
			
			$name 		  = "App_Model_DBTable_".$this->_domain_name;
			$this->_table = new $name();
		}
        return $this->_table;
    } 
	
	public function save(array $data)
    {
		$table  = $this->getTable();
			
        $fields = $table->info(Zend_Db_Table_Abstract::COLS);
        foreach ($data as $field => $value) {
            if (!in_array($field, $fields)) {
                unset($data[$field]);
            }
        }
        return $table->insert($data);
    }
	
	public function update($id,array $data)
	{
		
		foreach($data as $fieldname=>$field){
			if (substr($fieldname,strlen($fieldname)-4,4) == "_raw")
				unset($data[$fieldname]);
		}
		
		$table = $this->getTable();
		
		$where = $table->getAdapter()->quoteInto('id = ?', $id);
		
		$table->update($data, $where);
	}
	
	public function delete($id)
	{
		$table = $this->getTable();

		$where = $table->getAdapter()->quoteInto('id = ?', $id);

		$table->delete($where);
	}
	
	public function fetchPrevious($id,$where=NULL){
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = array('id'=>"MAX(id)");
		
		$select->from(array('z'=>$this->_getDBTableName()),$fields);
		$select->where('id < ?',$id);
		
		if ($where !== NULL)
			$select->where($where);
		
		$this->Log($select->assemble());

		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ((NULL !== $rows) && (isset($rows[0])))
		{
			return $rows[0]['id'];
		}
		else
			return NULL;
		
	}
	
	public function fetchNext($id,$where=NULL){
		
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = array('id'=>"MIN(id)");
		
		if ($where !== NULL)
			$select->where($where);
		
		$select->from(array('z'=>$this->_getDBTableName()),$fields);
		$select->where('id > ?',$id);
		
		$this->Log($select->assemble());	

		
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ((NULL !== $rows) && (isset($rows[0])))
		{
			return $rows[0]['id'];
		}
		else
			return NULL;
		
	}
	
	public function fetchEntries($order=NULL,$sort='asc',$joins=NULL,$filter=NULL,$debug=false)
    {
    	
    	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = $table->getFields();
		
		$select->from(array('z'=>$this->_getDBTableName()),$fields);
		
		
		if ($joins !== NULL){
			$alias = 'a';
			foreach($joins as $join){
				
				$select->joinLeft(array($alias=>$join['table']),"z.".$join['field']." = $alias.id",$join['joinfields']);
				$alias++;
			}			
		}
		if ($filter !== NULL){
			foreach($filter as $filtervalue){			
				foreach($filtervalue as $field=>$value){
					$select->where("$field = ?",$value);
				}
			}
		}
    	if ($order !== NULL){
			$fields = explode(",",$order);
			foreach($fields as $field)
				$select->order("$field $sort");
		}

		$this->Log($select->assemble());		
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
    }
	
	public function fetchFtEntries($ft_fields,$keyword,$order=NULL,$sort='asc',$joins=NULL,$filter=NULL,$debug=false)
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = $table->getFields();
		
		$select->from(array('z'=>$this->_getDBTableName()),$fields);
		
		$bFist = true;
		foreach($ft_fields as $ft_field){
			if ($bFist){
				$fields = "$ft_field";
				$bFist  = false;
			}
			else
				$fields = ",`$ft_field`";
		}
		$select->where("match($fields) against(? IN BOOLEAN MODE)",$keyword);
		
		if ($joins !== NULL){
			$alias = 'a';
			foreach($joins as $join){
				
				$select->joinLeft(array($alias=>$join['table']),"z.".$join['field']." = $alias.id",$join['joinfields']);
				$alias++;
			}			
		}	

		if ($filter !== NULL){			
			foreach($filter as $field=>$value)
				$select->where("$field = ?",$value);
		}
		
		if ($order !== NULL)
			$select->order("$order $sort");

		$this->Log($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
	}

    public function fetchEntry($id)
    {	
			
		if ($id === NULL)
			throw new Exception("Model_Table::fetchEntry : id parameter = NULL");
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = $table->getFields();
		
		$select->from(array('z'=>$this->_table_name),$fields);
		$select->where('id = ?', $id);
		
		$this->Log($select->assemble());	
		
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ((NULL !== $rows) && (isset($rows[0])))
		{
			return $rows[0];
		}
		else
			return NULL;		
    }
    
	public function fetchWhereGroupHasElements($group_elements_table_name,$group_by_fieldname)
	{
    	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
				
		$select->from(array('t'=>$this->_table_name));
		$select->where("select count(*) from `$group_elements_table_name` where $group_by_fieldname = t.id");
		
		
						
		$this->Log($select->assemble());
					
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if ($rows === NULL)
			return array();
		else 	
			return $rows;    	
    }
    
    protected function formatDate($date){
    	
    	$d  = strtotime($date);
    	return date("Y-m-d",$d);
    }
    
    protected function createhash($length=25){
		
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
	    srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;
		while ($i <= $length) {

        	$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}
	
	public function isUniqueName($uniquename,$tablename,$fieldname,$id=NULL)
    {
       	$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();

		$select->from(array('t'=>$tablename));
		$select->where($fieldname." = ?",$uniquename);
		
		if ($id !== NULL)
			$select->where('id <> ?',$id);
		
		$this->Log($select->assemble());			
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		if (count($rows) > 0)
			return true;
		else
			return false;
    }
}