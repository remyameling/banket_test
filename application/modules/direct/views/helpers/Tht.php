<?php

class Direct_View_Helper_Tht extends Uitval_View_Helper_Uitval
{
	public function Tht($date)
   	{
		$date = explode("-",$date);
		$date = str_pad($date[0],2,"0",STR_PAD_LEFT)."-".str_pad($date[1],2,"0",STR_PAD_LEFT)."-".$date[2];
	
   	 	return '<span style="width:7em;text-align:right;display:inline-block;float:left;">'.$date.'</span>';
   	}
}

?>