<?php

class Direct_View_Helper_EniacTime extends Uitval_View_Helper_Uitval
{
	public function EniacTime($eniactime)
   	{
   		$eniactime = str_pad($eniactime,6,'0',STR_PAD_LEFT);
   		
   		return substr($eniactime,0,2).":".substr($eniactime,2,2);
   	}
}

?>