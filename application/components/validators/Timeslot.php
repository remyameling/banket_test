<?php

class My_Validate_Timeslot extends Zend_Validate_Abstract
{
    const NOT_VALID  		= 'notValid';
    const NOT_VALID_TIME  	= 'notValidTime';
    
    private $_start;

    protected $_messageTemplates = array(
    	self::NOT_VALID_TIME  => 'Incorrecte tijd. Gebruik het formaat hh:mm',
        self::NOT_VALID  => 'eindtijd is eerder dan begintijd; gebuik >hh:mm voor eindtijden op volgende dag'
    );

    public function isValid($value, $context = null)
    {
    	
    		if( !preg_match('/^(>?([0-9])|>?([0-1][0-9])|>?([2][0-3])):([0-5][0-9])$/', $value)) {
    			$this->_error(self::NOT_VALID_TIME);
				return false;	
    		}
    	
    		// check if start time < end time
    		
    		$start 		= $context[$this->_start];
    		$end    	= $value;
    		$nextday 	= false;
    		$today 		= date("Y-m-d");
	    	$tomorrow	= date("Y-m-d",strtotime("+1 day"));
	    	 
			
    		if (substr($end,0,1) == '>'){
				$end 	 = substr($end,1);
				$nextday = true;
			}
			   		
    		$sp 		= explode(":",$start);
	    	$ep 		= explode(":",$end);
	    	
	    	$start = $today." ".$start.":00";
	    	if ($nextday)
	    		$end = $tomorrow." ".$end.":00";
	    	else
	    		$end = $today." ".$end.":00";
	    		
	    	$diff = (strtotime($end)-strtotime($start))/60;
	    	
	    	if ($diff <= 0){
    			$this->_error(self::NOT_VALID);
	    		return false;
	    	}
	    	else
	    		return true;				    	
    }
    
	public function __construct($options)
    {
        if ($options instanceof Zend_Config)
        {
            $options = $options->toArray();
        }

        if (!array_key_exists('start', $options)){
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Missing option. 'start' has to be given");
        }

        $this->setStart($options['start']);
    }
    
	public function setstart($elementName)
    {
    	$this->_start = $elementName;
        return $this;
    }
}

?>