<?php

class My_Validate_PasswordValid extends Zend_Validate_Abstract
{
    const NOT_VALID  = 'notValid';
    
    protected $_messageTemplates = array(
        self::NOT_VALID => 'Password not valid'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        if (is_array($context)) {	
		
            if (isset($context['id']))
            {
            	$model   = new App_Model_User();
        		$data	 = $model->fetchEntry($context['id']);
        		
        		if ($data !== NULL)
        		{
        			if ($data['user_password'] == $value)
 					 	return true;
        		}
            }
            else
        		throw new Exception("My_Validate_PasswordValid::isValid(): userid value (id) niet gevonden in from context");
        }
        else
        	throw new Exception("My_Validate_PasswordValid::isValid(): context is not an array");
        
        $this->_error(self::NOT_VALID);
        return false;
    }
}

?>