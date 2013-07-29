<?php 

abstract class RACCMS_Component_BaseController extends Zend_Controller_Action
{
	const FORM_ACTIONSTACK  = -4;	/* action pushed to action stack */
	const FORM_OTHER	    = -3;	/* ander formulier gepost dan dit formulier (bij multiple actions) */
	const FORM_NOT_POSTED   = -2;
	const FORM_INVALID  	= -1;
	const FORM_HANDLED  	= 0;
	
	protected $_form 			= NULL;
	protected $_logGroup 		= "CONTENT";
	protected $_currentSiteID	= NULL;
	protected $log				= NULL;
	
	public function init()
    {
    	$this->log = new App_Logger();						  
        return parent::init();
    }
	
	public function isImage($mime_type)
   	{
   		if (($mime_type == Zend_Registry::getInstance()->types->images->jpeg) ||
   		    ($mime_type == Zend_Registry::getInstance()->types->images->gif)  ||
   		    ($mime_type == Zend_Registry::getInstance()->types->images->png))
   		{
   		 	return true;   	
   		}
   		else
   			return false;
   	} 
   	
	protected function date2Euro($date)
    {
    	return substr($date,8,2)."-".substr($date,5,2)."-".substr($date,0,4);
    }
    
    protected function date2Iso($date)
    {
    	return substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    }
   	
	protected function createhash($length=25){
		
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
	    srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;
		while ($i <= $length) {

        	$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}
    
	protected function initLayout($layout_file,$layout_directory)
	{
		if (!file_exists(realpath($layout_directory)))
			die("Layout folder bestaat niet");
		
    	if (!file_exists(realpath($layout_directory)."/".$layout_file.".phtml"))
    		die("RACCMS_Component_BaseController::initLayout(): Layout file bestaat niet: ".realpath($layout_directory)."/$layout_file.phtml");
    		
    	//echo "layout:".$layout_directory.$layout_file."<br />";
    	
    		
    	$this->_helper->layout->setLayout($layout_file)
							  ->setLayoutPath(realpath($layout_directory));		
    }
	
	
	
	private function LogMsg($msg,$prio){
		
		if (is_array($msg))
			$msg = implode(",",$msg);
    	
    		
    	$function = "";
    	$class    = "";
    		
    	if (Zend_Registry::getInstance()->logging->logcaller){
    	
	    	$trace=debug_backtrace();
			
			$caller=array_shift($trace);
			$caller=array_shift($trace);
			$caller=array_shift($trace);
	
			$function = $caller['function'];
			if (isset($caller['class']))
				$class = $caller['class']."::";
    	}

    	Zend_Registry::getInstance()->logger->log($class.$function." ".$msg,$prio);
    	    	
    }
    
	protected function Log($msg)			// Debug: debug messages
	{
		$this->LogMsg($msg,Zend_Log::DEBUG);
	}
	
	protected function LogNotice($msg)		// Notice: normal but significant condition
	{
		$this->LogMsg($msg,Zend_Log::NOTICE);
	}
	
	protected function LogError($msg)		// Error: error conditions
	{
		$this->LogMsg($msg,Zend_Log::NOTICE);
	}
	
	protected function LogAlert($msg)		// Alert: action must be taken immediately
	{
		$this->LogMsg($msg,Zend_Log::ALERT);
	}
	
	protected function _getRequiredParam($name)
	{
		$request = $this->getRequest();
		$value   = $request->getParam($name,NULL);
		
		if ($value === NULL)
			throw new Exception("Parameter $name is verplicht en niet aangetroffen");
		else
			return $value;
	}
	
	protected function _getOptionalParam($name,$value)
	{
		$request = $this->getRequest();
		$value   = $request->getParam($name,$value);
		
		return $value;
	}
	
	protected function _setHeaders($filename,$type){
    	
    	$this->getResponse()->clearBody();
		$this->getResponse()->clearAllHeaders();
		$this->getResponse()->setHeader('Content-Type', $type ,true);
		$this->getResponse()->setHeader('Expires','Mon, 26 Jul 2030 05:00:00 GMT',true);
		$this->getResponse()->setHeader('Cache-Control', 'public',true);
		$this->getResponse()->setHeader('Content-Disposition','inline; filename='.basename($filename));
    }
    
    protected function _getTemplateBase(){
		
		return (APPLICATION_PATH."/".
    			Zend_Registry::getInstance()->paths->templates_base.
    			WEBSITE."/");
	}
	
	protected function _getUploadDirectory(){
		return realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_upload).DIRECTORY_SEPARATOR;
	}
    
    protected function _getRole(){
    	return App_Auth_Auth::getInstance()->getRole();
    }
    
	protected function _getCurrentUserId(){
    	return App_Auth_Auth::getInstance()->getIdentityId();
    }
    
	protected function _getCurrentSiteId()
	{
		if ($this->_currentSiteID === NULL){
		
			$data = App_ModelFactory::getModel("user")->fetchEntry(App_Auth_Auth::getInstance()->getIdentityId());
	    	if (isset($data['user_site_id']))
	    		$this->_currentSiteID = $data['user_site_id'];
	    	else
	    		$this->_currentSiteID = NULL;
		}
		
		return $this->_currentSiteID;
    }
    
	protected function _getCurrentSiteName()
	{
    	$site_id = $this->_getCurrentSiteId();
    	if ($site_id !== NULL)
    		return Zend_Registry::getInstance()->sites->get($site_id);
    	else
    		return NULL;
    }
    	    
	protected function _isAllowed($resource,$privilege){
    	
		if (Zend_Registry::getInstance()->acl->isAllowed($this->_getRole(), $resource, $privilege))
			return true;
		else
			return false;
    }
    
	protected function _getForm($controller_name,$form_name)
    {
		if (NULL === $this->_form)
			$this->_form = new RAC_Component_Form($this->_getFormConfig()->get(strtolower($controller_name)),$form_name);
			
		return $this->_form;
	}

	protected function _handleForm($form,$default_data,$process_func,$action=NULL){
	
		assert($form !== NULL);
		assert($process_func !== NULL);
		
		$request = $this->getRequest();
		assert($request !== NULL);
		
		if ($default_data !== NULL){
			$form->setDefaults($default_data);
			//$this->p($default_data);
		}
			
		if ($action !== NULL)
			$form->setAction($action);
			
		// assign the form to the view
		$this->view->form = $form;	
		
		// check to see if this action has been POST'ed to
		if ($this->getRequest()->isPost()) {
			
			// check if this form is the posted form
			//assert(isset($_POST['frmid']));				// frmid zou altijd aanwezig moeten zijn
														// (of alleen voor RAC_Forms ?)
			
			if ($form->getAttrib('id') != $_POST['frmid'])
				return self::FORM_OTHER;				// ander formulier gepost dan af te handelen formulier
			
			// now check to see if the form submitted exists, and
			// if the values passed in are valid for this form
			if ($form->isValid($request->getPost())) {
				
				return $this->$process_func($form);	
			}
			else
				return self::FORM_INVALID;
		}
		else
			return self::FORM_NOT_POSTED;			
	}
	
	protected function getAuthAdapter(array $params,$type="user")
    {
    	switch($type)
    	{
    		case 'user':
    			$adapter = new App_Auth_AuthAdapterMembers(Zend_Registry::getInstance()->dbAdapter,
													 'user','user_name','user_password',"? and user_active = 1");
    			$adapter->setIdentity($params['username']);
    			$adapter->setCredential($params['password']);
    			break;
    		case 'friend':
    			$adapter = new App_Auth_AuthAdapterFriends(Zend_Registry::getInstance()->dbAdapter,
    												'friend','id','friend_hash',NULL);
				$adapter->setIdentity($params['id']);
				$adapter->setCredential($params['friend_hash']);
				break;
    		default:
    			die("unknown authorization type:$type");
    			break;
    	}
		
		return $adapter;
    }
    
	protected function renderActionIcon($title,$iconname,$params,$action=NULL,$controller=NULL,$module=NULL,$name=NULL,$reset=true)
    {
    	if ($controller !== NULL)
    		$params['controller'] = $controller;
    	else
    		$params['controller'] = $this->getRequest()->getControllerName();
    		
    	if ($action !== NULL)
    		$params['action'] = $action;
    	else
    		$params['action'] = $this->getRequest()->getActionName();
    		
    		
    	if ($module !== NULL)
    		$params['module'] = $module;
    	else
    		$params['module'] = $this->getRequest()->getModuleName();
    		
    	$url     = $this->view->url($params,$name,$reset);
    	
    	$output  = '<div class="iconwrapper ui-corner-all">'.PHP_EOL;
    	$output .= '<a href="'.$url.'" class="ui-icon '.$iconname.'" title="'.$title.'">';
    	$output .= $title;
    	$output .= '</a></div>'.PHP_EOL;
    	
    	return $output;
    }
    
	protected function getDateRange($date,$dienst=NULL,$format="Y-m-d H:i:s")
	{
		$site_name  = $this->_getCurrentSiteName();
		$num_shifts = count(Zend_Registry::getInstance()->sites->dienst->get($site_name)->toArray());
		$today      = $date;
		$todatt     = mktime(0,0,0,(int) substr($today,3,2),(int) substr($today,0,2),(int) substr($today,6,4));
		$tomorrow   = date("d-m-Y",strtotime("+1 day",$todatt));
		
		//echo "today:$today; tomorrow:$tomorrow<br />";
		
		foreach(Zend_Registry::getInstance()->sites->dienst->get($site_name)->start->toArray() as $id=>$start)
		{
			$end 	= Zend_Registry::getInstance()->sites->dienst->get($site_name)->einde->get($id)->toArray();
			
			$sh 	= $start['uur'] % 24;
			$sm 	= $start['minuten'];
			$ss 	= 0;
			$sdate  = $start['uur'] < 24 ? $today : $tomorrow;
			
			$eh 	= $end['uur'] % 24;
			$em 	= $end['minuten'];
			$es 	= 0;
			$edate  = $end['uur'] < 24 ? $today : $tomorrow;
			
			//echo "sh,sm,ss,sdate = $sh,$sm,$ss,$sdate<br >";
			//echo "eh,em,es,edate = $eh,$em,$es,$edate<br >";			
			//die();
			
			$tstart 		=  mktime($sh,$sm,$ss,(int) substr($sdate,3,2),(int) substr($sdate,0,2),(int) substr($sdate,6,4));
			$tend   		=  mktime($eh,$em,$es,(int) substr($edate,3,2),(int) substr($edate,0,2),(int) substr($edate,6,4));
			$ranges[$id]	=  array('start'=>$tstart,'end'=>$tend);
		}
		
		if ($dienst === NULL){
			$first = array_shift($ranges);
			$last  = array_pop($ranges);

			$start = $first['start'];
			$end   = $last['end'];
		}
		else{
			$start = $ranges[$dienst]['start'];
			$end   = $ranges[$dienst]['end'];
		}
		
		$range = array('min'=>date($format,$start),'max'=>date($format,$end));
		
		return $range;		
	}
	
	private function _convertConfigTimes($times){
		
		// converteert een array van tijden in het formaat array('uur','minuten') naar "HH:mm"
		
		$converted = array();
		foreach($times as $idx=>$time){
			$converted[$idx] = str_pad($time['uur'],2,'0',STR_PAD_LEFT).":".str_pad($time['minuten'],2,'0');
		}
		return $converted;
	}
	
	private function _shiftTime($times,$shift){
		
		// $times is een array of string van tijd in het formaat HH:mm
		// $shift is een string van tijd in het formaat HH:mm
		// return een array of een string van tijden waarbij $shift is afgetrokken van de tijd(en) in $times 
		
		$ret_array 	= true;
		$shifted 	= array();
		$delta   	= (((int) substr($shift,0,2))*60+(int) substr($shift,3,2))*60;	// $delta in timestamp sec.
	
		if (!is_array($times)){		// als $times geen array is, maak er een array van
			$timearr[0]  = $times;
			$ret_array = false;
		}
		else
			$timearr = $times;
		
		foreach($timearr as $idx=>$time){	// voor iedere time in $times
			
			$hour   = substr($time,0,2);
			$min    = substr($time,3,2);
			
			$torg 	= mktime($hour,$min,0,date("n"),date("j"),date("Y"));	// tijd in timestamp
			$tnew   = $torg-$delta;											// bereken delta
			$shifted[$idx] = date("H:i",$tnew);								// nieuwe tijd in formaat "HH:mm"
		}
		
		if ($ret_array)
			return $shifted;
		else
			return $shifted[0];
		
	}
	
	private function _inTimeWindow($start,$end,$time){
		
		// bepaal of $time in het interval [$start,$end> ligt
		
		$ts	= mktime(substr($start,0,2),substr($start,3,2),0);	// start timestamp
		$te = mktime(substr($end,0,2),substr($end,3,2),0);		// end timestamp
		
		if ($te < $ts)											// indien end < start, dan tel er 1 dag in seconden bij op
			$te = $te+24*60*60;
		
		$t 	= mktime(substr($time,0,2),substr($time,3,2),0);	// time timestamp
		
		if (($t >= $ts) && ($t < $te))							// indien time in interval
			return true;										// ret. true
		else 	
			return false;
		
	}
	
	protected function getCurrentDienstId()
	{
		$site_name  	= $this->_getCurrentSiteName();																						// sitenaam
		$starttimes     = $this->_convertConfigTimes(Zend_Registry::getInstance()->sites->dienst->get($site_name)->start->toArray());		// starttijden
		$endtimes     	= $this->_convertConfigTimes(Zend_Registry::getInstance()->sites->dienst->get($site_name)->einde->toArray());		// eindtijden
		$shift			= $starttimes[1];																									// timeshift
		
		$timenow        = $this->_shiftTime(date("H:i"),$shift);	// huidige tijd, shifted		
		$starttimes     = $this->_shiftTime($starttimes,$shift);	// starttijden, shifted
		$endtimes     	= $this->_shiftTime($endtimes,  $shift);	// eindtijden, shifted
				
		$dienst 		= -1;
		foreach($starttimes as $idx=>$start){			
			if ($this->_inTimeWindow($starttimes[$idx],$endtimes[$idx],$timenow))	// indien huidige tijd in tijdsinterval
				$dienst = $idx;														// dienst gevonden
		}
		return $dienst;
	}
	
	protected function removeHiddenFields($form,$fields,$current_site_name){
		
		$elements = $fields->elements->toArray();
		if (!empty($elements)){
			foreach($elements as $element){
				$sites = $fields->get($element)->sites->toArray();
				if (!empty($sites)){
					foreach($sites as $site){
						if ($site == $current_site_name){
							$form->removeElement($element);
						}
					}
				}				
			}
		}	
		return $form;
	}   	
    	
    
	protected function p($data,$bDie=false){
		
		return $this->log->p($data,$bDie);

		echo '<pre style="display:inline-block;background-color:#f00;color:#fff;padding:0.5em;font-size:11px;">';
		Zend_debug::dump($data); //print_r($data);
		echo '</pre>';
	
		if ($bDie)
			die("===================== DIED ===================");
	}
}