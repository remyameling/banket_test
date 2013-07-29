<?php

class Uitval_View_Helper_Gewicht extends Uitval_View_Helper_Uitval
{
	public function Gewicht($gewicht,$postfix=" Kg")
   	{
   		return number_format($gewicht/10,1,",","").$postfix;
   	}
}

?>