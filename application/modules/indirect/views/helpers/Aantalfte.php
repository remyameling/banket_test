<?php

class Indirect_View_Helper_Aantalfte extends Zend_View_Helper_Abstract
{
	public function Aantalfte($data,$dienst_id,$functie_id)
   	{
   		if (isset($data[$dienst_id][$functie_id]))
   			return number_format($data[$dienst_id][$functie_id]['aantal_fte']/100,2,",",".");
   		else
   			return "";
   	}
}

?>