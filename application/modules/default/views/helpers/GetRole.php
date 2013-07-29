<?php

class Default_View_Helper_GetRole extends Zend_View_Helper_Abstract
{
	public function GetRole()
   	{
		return App_Auth_Auth::getInstance()->getRole();
   	}
}