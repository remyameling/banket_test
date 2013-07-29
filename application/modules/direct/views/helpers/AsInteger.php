<?php

class Direct_View_Helper_AsInteger extends Uitval_View_Helper_Uitval
{
	public function AsInteger($float)
   	{
   		return '<span style="width:5em;text-align:right;display:inline-block;float:left;">'.number_format($float,0,",",".").'</span>';
   	}
}

?>