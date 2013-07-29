<?php

class App_Model_DBTable_Table extends Zend_Db_Table_Abstract
{
    /* Table name */
    protected $_name = '';   
    protected $_meta_data = NULL;
	
	public function set_table_name($tableName){
		$this->_name = $tableName;
	}
	
	public function get_table_name(){
		return $this->_name;
	}	
	
	private function _getMetaData(){
		if ($this->_meta_data === NULL){
			$this->_meta_data = $this->info();
		}
		return $this->_meta_data;
	}
	
	private function isDateField($fieldname){
		$metadata = $this->_getMetaData();
				
		return $metadata['metadata'][$fieldname]['DATA_TYPE'] == 'date' ? true : false;
	}

	private function isDateTimeField($fieldname){
		$metadata = $this->_getMetaData();
				
		if (($metadata['metadata'][$fieldname]['DATA_TYPE'] == 'datetime') ||
		    ($metadata['metadata'][$fieldname]['DATA_TYPE'] == 'timestamp'))
			return true;
		else
			return false;
	}
	
	private function isBoolField($fieldname){
		$metadata = $this->_getMetaData();
		
		return $metadata['metadata'][$fieldname]['DATA_TYPE'] == 'tinyint' ? true : false;
	}
	
	public function getFields(){
		
		$info = $this->info();
		$cols = array();
		
		foreach($info['metadata'] as $item){
			
			$field_name = $item['COLUMN_NAME'];
			
			if ($this->isDateField($field_name)){
				$cols[$field_name]        = "date_format($field_name,'%d-%m-%Y')";
				$cols[$field_name."_raw"] = $field_name;
			}
			else if ($this->isDateTimeField($field_name)){
				$cols[$field_name] = "date_format($field_name,'%d-%m-%Y (%H:%i:%s)')";
				$cols[$field_name."_raw"] = $field_name;
			}
			else
				$cols[]=$item['COLUMN_NAME'];			
		}
		
		return $cols;
	}
	
	protected function format_amount($amount){
		
		if (($amount !== NULL) && ($amount != ""))
		{
			// remove digit seperator

			$amount = str_replace(",","",$amount,$count);
			
			// if no seperator was provided, add 2 trailing zeros
			
			if ($count == 0)
				$amount = $amount*100;			
		}
		
		return $amount;
	}
	
	private function format_date($date){
		return substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
	}
	
	public function update($data,$where){
		
		foreach($data as $field_name => $field_value){
			if ($this->isDateField($field_name)){
				if ($field_value != '')
					$data[$field_name] = $this->format_date($data[$field_name]);
				else
					$data[$field_name] = NULL;	
			}
		}
		
		parent::update($data,$where);
	}
	
	public function insert($data){
		
		foreach($data as $field_name => $field_value){
			if ($this->isDateField($field_name)){
				if ($field_value != '')
					$data[$field_name] = $this->format_date($data[$field_name]);
				else
					$data[$field_name] = NULL;	
			}
		}
		
		return parent::insert($data);
	}
	
}