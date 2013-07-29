<?php

class Default_View_Helper_FormatDbAmount extends Zend_View_Helper_Abstract
{
	public function FormatDbAmount($amount,$dec_point=",",$thousands_sep=".",$prefix="")
   	{
		$amount 	= $amount/100;
		$str_amount = number_format($amount,2,$dec_point,$thousands_sep);
   		
        return $prefix.$str_amount;
   	}
}