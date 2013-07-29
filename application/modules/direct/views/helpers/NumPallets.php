<?php

class Direct_View_Helper_NumPallets extends Uitval_View_Helper_Uitval
{
	public function NumPallets($num,$postfix=" Pallets")
   	{
   		return '<span style="width:5em;text-align:right;display:inline-block;float:left;">'.$num.$postfix.'</span>';
   	}
}

?>