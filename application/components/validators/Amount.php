<?php

class My_Validate_Amount extends Zend_Validate_Abstract
{
    const NOT_VALID  = 'notValid';

    protected $_messageTemplates = array(
        self::NOT_VALID  => 'Dit is geen geldig bedrag. (gebruik de komma optioneel, als separator)'
    );

    public function isValid($value, $context = null)
    {
    	// check if value is a valid Amount  68,19 (comma seperated cents, with optional 2 digits)
    		
    	if( !preg_match('/^[0-9\-]+(,[0-9][0-9])?$/', $value)) {
    			$this->_error(self::NOT_VALID);
				return false;	
    	}
    	return true;
    }
}

?>