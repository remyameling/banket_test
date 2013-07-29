<?php

class Default_View_Helper_FormatDescription extends Zend_View_Helper_Abstract
{
	public function FormatDescription($description)
   	{
		return trim(strip_tags($description));
   	}
}