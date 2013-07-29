<?php

class My_Validate_MinimumGewicht extends Zend_Validate_Abstract
{
    const NOT_OK = 'notOk';
    
    protected $_messageTemplates = array(
        self::NOT_OK  => 'Het gewicht moet minimaal %min% Kg zijn',
    );
    
    protected $_messageVariables = array(
        'min' => '_min'
    );
    
    protected $_min;

    public function isValid($value, $context = null)
    {
    	$data = App_ModelFactory::getModel("user")->fetchEntry(App_Auth_Auth::getInstance()->getIdentityId());
    	
    	if (isset($data['user_site_id']))
    	{
    		$site_id 	= $data['user_site_id'];
    		$site_naam	= Zend_Registry::getInstance()->sites->get($site_id);
    	}
    	else
    		throw new Exception("Kan niet de huidige produktie site bepalen.");
    	
    	$value 		= (int) $value;
        $this->_min = Zend_Registry::getInstance()->uitval_consts->bak->get($site_naam)->get($context['baktype'])->gewicht / 10; 
        
        $error 		= self::NOT_OK;
        
        if ($value < $this->_min)
		{
			$this->_error($error);
			return false;
		}        
		else
			return true;
    }
}

?>