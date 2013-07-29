<?php

class My_Validate_ArtikelCode extends Zend_Validate_Abstract
{
    const NOT_VALID  = 'notValid';

    protected $_messageTemplates = array(
        self::NOT_VALID  => 'Dit is een ongeldige artikel barcode'
    );

    public function isValid($value, $context = null)
    {
    	if (strlen($value) == 13)
    		$validator = new Zend_Validate_Barcode('EAN13');
    	else
    		$validator = new Zend_Validate_Barcode('EAN8');
    		
		$bValid = $validator->isValid($value);
		
		if (!$bValid){
			$this->_error(self::NOT_VALID);
			return false;	
		}
		else
			return true;
		
    }
}

?>