<?php

class App_View_Helper_Tijd extends Zend_View_Helper_Abstract
{
	public function Tijd($tijd)
   	{
   		return substr($tijd,11);
   	}
}

?>