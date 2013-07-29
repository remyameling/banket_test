<?php

class App_View_Helper_Datum extends Zend_View_Helper_Abstract
{
	public function Datum($datetime)
   	{
   		$datetime = substr($datetime,0,10);
   		   		
   		$parts = explode("-",$datetime);
   		
   		return str_pad($parts[2],2,'0',STR_PAD_LEFT)."-".str_pad($parts[1],2,'0',STR_PAD_LEFT)."-".$parts[0];
   	}
}

?>