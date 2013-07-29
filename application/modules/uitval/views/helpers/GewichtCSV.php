<?php

class Uitval_View_Helper_GewichtCSV extends Uitval_View_Helper_Uitval
{
	public function GewichtCSV($gewicht)
   	{
   		return number_format($gewicht/10,1,",","");
   	}
}

?>