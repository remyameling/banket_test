<?php

class App_Logger
{
	private $callerID 	= NULL;
	private $functionID = NULL;
	private $logger   	= NULL;
	
	public function __construct($callerID=""){
		
		$this->callerID = $callerID;
		
		if ($this->callerID != "")
			$this->callerID .= ":";
		
		$this->logger   = Zend_Registry::getInstance()->logger;		
	}	
	
	private function composeMessge($data){
		
		if (is_array($data))
			$data = print_r($data, true);
		
		$trace = debug_backtrace();
		
		$caller = array_shift($trace);
		$caller = array_shift($trace);
		$caller = array_shift($trace);
		
		$function = $caller['function'];
		if (isset($caller['class']))
			$class = $caller['class']."::";
		else
			$class = "";
		
		return $class.$function." ".$data;		
	}
	
	public function l($data,$level=Zend_Log::DEBUG){
				
		$this->logger->log($this->composeMessge($data),$level);
	}	

	public function log($data,$level=Zend_Log::DEBUG){
		
		$this->logger->log($this->composeMessge($data),$level);
	}
	
	public function p($data,$bDie=false){
		
		$msgpre   = '<pre style="display:inline-block;background-color:#f00;color:#fff;padding:0.5em;">';
		
		if (is_array($data))
			$data = print_r($data, true); 
		
		$msgdat   = $data;
		$msgpost  = '</pre>';
		
		echo $msgpre.$msgdat.$msgpost;
	
		if ($bDie)
		{
			
			die("===================== DIED ===================");
			throw new Exception("die");
		}
		else
			$this->logger->log($this->composeMessge($msgdat),Zend_Log::ALERT);
	}

}

?>

