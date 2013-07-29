<?php

class My_Validate_ValidArtikelCode extends Zend_Validate_Abstract
{
    const NOT_VALID  = 'notValid';

    protected $_messageTemplates = array(
        self::NOT_VALID  => 'Artikel komt niet overeen met palletbon. Waarschuw uw teamleider'
    );

    public function isValid($value, $context = null)
    {
		$sscc 					= $context['sscc'];
		$klant_artikel_nummer 	= substr($sscc,14,3);
		
		$mdl 	= new App_Model_Verpakking();
		$data 	= $mdl->fetchByKlantArtikelNummer($klant_artikel_nummer);
		
		if (isset($data['barcode_consument']))
		{
			if ($value == $data['barcode_consument'])
				return true;
			else
			{
				$this->_error(self::NOT_VALID);
				return false;
			}
		}
		else
			return true;
    }
}

?>