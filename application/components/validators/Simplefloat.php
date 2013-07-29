<?php

class My_Validate_Simplefloat extends Zend_Validate_Abstract
{
    const NOT_VALID  = 'notValid';

    protected $_messageTemplates = array(
        self::NOT_VALID  => 'Ongeldig: Gebruik de komma als decimaal scheidingsteken, maximaal 1 cijfer achter de komma'
    );

    public function isValid($value, $context = null)
    {
    	// Float met maximaal 1 cijfer achter de komma
    		
    	if( !preg_match('/^[0-9\-]+(,[0-9])?$/', $value)) {
    			$this->_error(self::NOT_VALID);
				return false;	
    	}
    	return true;
    }
}

?>