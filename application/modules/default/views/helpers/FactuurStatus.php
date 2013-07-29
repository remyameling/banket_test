<?php

class Default_View_Helper_FactuurStatus extends Zend_View_Helper_Abstract
{
	public function FactuurStatus($factuur_data=NULL)
   	{
   		assert(isset($factuur_data['id']));
   		$factuur_id = $factuur_data['id'];
   		
   		if (($factuur_data === NULL) || (!isset($factuur_data['factuur_total_raw'])))
   		{
   			
   			$fMdl 			= new App_Model_Factuur();
   			$factuur_data	= $fMdl->fetchEntryEx($factuur_id); 
   		}
   		
   		
   		
   		if ($factuur_data['factuur_status'] != Zend_Registry::getInstance()->consts->factuur->statusvalue->nieuw)
   		{
   			return Zend_Registry::getInstance()->consts->factuur->status->get($factuur_data['factuur_status']);
   		}
   		else
   		{
   			$date   	  = $factuur_data['factuur_datum'];
   			$num_days	  = $factuur_data['factuur_betalingstermijn'];
   		
   			$start 	  	  = mktime(0,0,0,substr($date,3,2),substr($date,0,2),substr($date,6,4));
			$verval_datum = strtotime("+$num_days days",$start);
			$now          = time();
		
			if ($now > $verval_datum)
   				return Zend_Registry::getInstance()->consts->factuur->status->get(40);
   			else
   				return Zend_Registry::getInstance()->consts->factuur->status->get(10);
   		}
   	}
}