<?php

class Direct_View_Helper_AantalFte extends Uitval_View_Helper_Uitval
{
	public function AantalFte($aantal)
   	{
   		return number_format($aantal/10,1,",","");
   	}
}

?>