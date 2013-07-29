<?php 

abstract class App_Model_Pallets extends App_Model_table
{
	protected function _Init($site_name)
	{
		
	}
	
	abstract public function fetchMagazijnpallets($date,$site_name,$order,$sort);
	abstract public function fetchByOrderAndCode($site_name,$artikelnr,$ordernr,$magazijncode,$order,$sort);  
	abstract public function fetchCountByOrdernum($site_name,$date,$order='ORDERNR',$sort='ASC');
   
}

?>