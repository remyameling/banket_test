<?php

class Default_View_Helper_FactuurVervaldatum extends Zend_View_Helper_Abstract
{
	public function FactuurVervaldatum($factuur_data,$format="d-m-Y",$locale="nl_NL",$winlocale='nld_nld')
   	{
   		$factuur_id = $factuur_data['id'];
   		assert($factuur_id !== NULL);
   		
   		if (($factuur_data === NULL) || (!isset($factuur_data['factuur_total_raw'])))
   		{
   			$fMdl 			= new App_Model_Factuur();
   			$factuur_data	= $fMdl->fetchEntryEx($factuur_id); 
   		}
   		
   		$date   	  = $factuur_data['factuur_datum'];
   		$num_days	  = $factuur_data['factuur_betalingstermijn'];
   		
   		$start 	  	  = mktime(0,0,0,substr($date,3,2),substr($date,0,2),substr($date,6,4));
		$verval_datum = strtotime("+$num_days days",$start);
		
		/* Set locale to Dutch */
		$res = setlocale(LC_ALL, $locale);
		if (!$res)	
			$res = setlocale(LC_ALL, $winlocale);
			
        return strftime($format,$verval_datum);
   	}
}