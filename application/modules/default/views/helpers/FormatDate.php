<?php

class Default_View_Helper_FormatDate extends Zend_View_Helper_Abstract
{
	public function FormatDate($date,$format="%A",$locale="nl_NL",$winlocale='nld_nld')
   	{
		/* Set locale to Dutch */
		
   		$exp         		= explode("-", substr($date,0,10));
        $uitruk_mktime      = mktime(0, 0, 0, $exp[1], $exp[0], $exp[2]);
        
        /* Set locale to Dutch */
		$res = setlocale(LC_ALL, $locale);
		if (!$res)	
			$res = setlocale(LC_ALL, $winlocale);
			
        return strftime($format,$uitruk_mktime);
           

   	}
}