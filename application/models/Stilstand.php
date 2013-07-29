<?php 

class App_Model_Stilstand extends App_Model_Table
{ 	
	
	function fetchByDate($range,$site_id,$lijn=NULL,$order='tijd',$sort='desc')
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
				
		$select->from(array('s'=>'stilstand'));
		$select->where('site_id = ?',$site_id);
		$select->where('tijd >= ?',$range['min']);
		$select->where('tijd < ?' ,$range['max']);
		
		if ($lijn !== NULL)
			$select->where("lijnnr = ?",$lijn);
		
		$select->order("$order $sort");
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		
		return $rows;
	}
	
	function fetchByLijnnr($range,$site_id,$dienst_id)
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('s'=>'stilstand'),array('minuten'=>'SUM(minuten)','lijnnr'));
		$select->where('site_id = ?',	$site_id);
		$select->where('dienst_id = ?',	$dienst_id);
		$select->where('tijd >= ?',		$range['min']);
		$select->where('tijd < ?' ,		$range['max']);
		$select->group('lijnnr');
		$select->order('lijnnr asc');
		
		//die($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
	}
	
	function fetchByCategoryAndLijnnr($range,$site_id)
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('s'=>'stilstand'),array('minuten'=>'SUM(minuten)','lijnnr','categorie'));
		$select->where('site_id = ?',	$site_id);
		$select->where('tijd >= ?',		$range['min']);
		$select->where('tijd < ?' ,		$range['max']);
		$select->group('categorie');
		$select->group('lijnnr');
		$select->order('lijnnr asc');
		$select->order('categorie asc');
		
		//die($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
	}
	
	/*
	function fetchByBak($range,$site_id,$bakken)
	{
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		assert(count($bakken) >= 1);
		
		$select->from(array('u'=>'uitval'),array('gewicht'=>'SUM(gewicht_netto)'));
		$select->where('site_id = ?',	$site_id);
		$select->where('tijd >= ?',$range['min']);
		$select->where('tijd < ?' ,$range['max']);
		
		$bakken = "(".implode(",",$bakken).")";
		
		$select->where("baktype in $bakken");
		
		$this->Log($select->assemble());		
		//die($select->assemble());
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		return $rows;
	}
	
	
	
	public function save(array $data)
    {
    	switch($data['baktype'])
    	{
    		case 0: $data['gewicht']=$data['gewicht']-Zend_Registry::getInstance()->consts->gewicht->rode_bak;
    			    break;
    		case 1: $data['gewicht']=$data['gewicht']-Zend_Registry::getInstance()->consts->gewicht->grijze_bak;
    				break;
    	}
    	
    	parent::save($data);
    }
    
	public function update($id,array $data)
    {
    	switch($data['baktype'])
    	{
    		case 0: $data['gewicht']=$data['gewicht']-Zend_Registry::getInstance()->consts->gewicht->rode_bak;
    			    break;
    		case 1: $data['gewicht']=$data['gewicht']-Zend_Registry::getInstance()->consts->gewicht->grijze_bak;
    				break;
    	}
    	
    	parent::update($id,$data);
    }
    
	public function fetchEntry($id)
    {	
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();
		
		$table  = $this->getTable();
		$fields = $this->getFields();
		
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
    
    private function getFields()
    {
    	$gewicht_rood  = Zend_Registry::getInstance()->consts->gewicht->rode_bak;
		$gewicht_grijs = Zend_Registry::getInstance()->consts->gewicht->grijze_bak;
		
		$fields = array('id','baktype','categorie','lijnnr','opmerkingen','tijd');		
		$fields['gewicht_netto'] = 'gewicht';
		$fields['gewicht_bruto'] = "(case when baktype = 0 then gewicht+$gewicht_rood else gewicht+$gewicht_grijs end)";
		$fields['gewicht'] 		 = "(case when baktype = 0 then gewicht+$gewicht_rood else gewicht+$gewicht_grijs end)";
		
		return $fields;
    }
	
	
	
	
	
	
	*/
}