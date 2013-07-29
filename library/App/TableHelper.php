<?php

class App_TableHelper
{
	private $_filters 			= array();
	private $_default_order 	= NULL;
	private $_default_sort  	= NULL;
	private $_owner				= NULL;
	private $_request			= NULL;
	private $_headerRows		= array();
	private $_bodyCols			= array();
	private $_itemcount_values  = array('10'=>10,'20'=>20,'50'=>50,'100'=>100,'250'=>250,'1000'=>1000,'Alles'=>100000);
	private $_entries			= NULL;
	private $_getentriescb		= NULL;
	
	const 	FILTER_SELECT			= 1;
	const 	FILTER_DATE				= 2;
	const   DEFAULT_GETENTRIESCB	= 'tabGetEntries';
	
	public function __construct($caller,$request,$config=NULL,$replaceVars=array())
	{
		$this->_owner		  = $caller;
		$this->_request		  = $request;
		
		if (!isset(Zend_Registry::getInstance()->session->misc->num_items_per_page))
			Zend_Registry::getInstance()->session->misc->num_items_per_page = '100000';		
		
		$num_items = $this->_request->getParam('ic',Zend_Registry::getInstance()->session->misc->num_items_per_page);
		Zend_Registry::getInstance()->session->misc->num_items_per_page = $num_items;
    	
    	$this->_owner->view->tableHelper = $this;
    	
    	if ($config !== NULL)
    	{
    		$this->setConfig($config,$replaceVars);
    		$this->postRedirectGet();
    	}
	}
	
	public function setConfig($config,$replaceVars=NULL)
	{
		$this->addHeader($config->header,$replaceVars);
    	$this->addBody($config->body);
    	
    	if (isset($config->default->order))
    	{
    		if (isset($config->default->sort))
    			$this->setDefaultOrder($config->default->order,$config->default->sort);
    		else
    			$this->setDefaultOrder($config->default->order);
    	}
    	if (isset($config->default->getentriescb))
    		$this->setGetEntries($config->default->getentriescb);
    	else if (!isset($this->_getentriescb))
    		$this->setGetEntries(self::DEFAULT_GETENTRIESCB);
	}
	
	public function setDefaultOrder($order,$sort='asc')
	{
		$this->_default_order = $order;
		$this->_default_sort  = $sort;
	}
	
	public function setGetEntries($functionName){
		
		if (!method_exists($this->_owner,$functionName))
			throw new Exception("Callback functie voor getEntries ($functionName) bestaat niet.");
		
		$this->_getentriescb = $functionName;
	}
	
	public function addHeader($rows,$replaceVars=array()){
		// converteer naar array indien nodig
		if ($rows instanceof Zend_Config)
			$rows = $rows->toArray();
			
		foreach($rows as $rownum=>$cols)
			$this->addHeaderRow($cols,$rownum,$replaceVars);
	}
	
	public function addBody($cols){
		
		// converteer naar array indien nodig
		if ($cols instanceof Zend_Config)
			$cols = $cols->toArray();
			
		// toevoegen kolommen
		foreach($cols as $col){
			
			$fieldname	= isset($col['fieldname']) ? $col['fieldname'] : NULL;
			$viewhelper	= isset($col['viewhelper']) ? $col['viewhelper'] : NULL;
			$formatfunc	= isset($col['formatfunc']) ? $col['formatfunc'] : NULL;
			$class	 	= isset($col['class']) ? $col['class'] : NULL;
			$colspan	= isset($col['colspan']) ? $col['colspan'] : NULL;			
			$prefix 	= isset($col['prefix']) ? $col['prefix'] : NULL;
			
			$this->addBodyCol($fieldname,$viewhelper,$formatfunc,$class,$prefix,$colspan);
		}
	}
	
	public function addHeaderCol($label,$sortfield=NULL,$row=0,$class="",$colspan=1){
		
		$col['label'] 		= $label;
		$col['sortfield']	= $sortfield;
		$col['class']		= $class;
		$col['colspan']		= $colspan;
		
		$this->_headerRows[$row][] = $col;
	}
	
	private function textReplace($text,$replacement)
	{
		if (!is_array($text)){
			foreach($replacement as $string => $replacement_string)
				
				if (!is_array($replacement_string)){
					$text = str_replace("%".$string."%",$replacement_string,$text);
				}
						
			return $text;
		}
	}
	
	public function addHeaderRow($cols,$row=0,$replaceVars=array()){
		
		// converteer naar array indien nodig
		if ($cols instanceof Zend_Config)
			$cols = $cols->toArray();
			
		// label text replacement
		if (count($replaceVars) > 0){			
			foreach($cols as $idx=>$val){
				$cols[$idx]['label'] = $this->textReplace($val['label'],$replaceVars);
			}
		}
		
		// toevoegen kolommen
		foreach($cols as $col){
			$label = $col['label'];
			$sortfield = isset($col['sortfield']) ? $col['sortfield'] : NULL;
			$class = isset($col['class']) ? $col['class'] : NULL;
			$colspan = isset($col['colspan']) ? $col['colspan'] : NULL;
			
			$this->addHeaderCol($label,$sortfield,$row,$class,$colspan);
		}
	}
	
	public function addBodyCol($fieldname,$viewhelper=NULL,$formatfunc=NULL,$class="",$prefix="",$colspan=1)
	{
		$col['fieldname']	= $fieldname;
		$col['viewhelper']	= $viewhelper;
		$col['formatfunc']	= $formatfunc;
		$col['class']		= $class;
		$col['prefix']		= $prefix;
		$col['colspan']		= $colspan;	
		
		$this->_bodyCols[] = $col;		
	}
	
	public function getHeaderRows(){
		return $this->_headerRows;
	}
	
	public function getBodyCols(){
		return $this->_bodyCols;
	}
	
	private function _getEntries(){
		if ($this->_entries === NULL){
			$functionName   = $this->_getentriescb;
			$this->_entries = $this->_owner->$functionName($this,$this->getOrder(),$this->getSort());
		}
			
		return $this->_entries;
	}
	
	public function getPagedEntries()
	{
		$page 		= $this->_request->getParam('page',1);
		$entries  	= $this->_getEntries();
		
		if ($entries === NULL)
			$entries = array();
		
		$paginator = Zend_Paginator::factory($entries);
	   	$paginator->setItemCountPerPage(Zend_Registry::getInstance()->session->misc->num_items_per_page);
	   	$paginator->setCurrentPageNumber($page);
	   		
	   	
		return $paginator;		
	}
	
	public function getAllEntries()
	{
		return $this->_getEntries();
	}
	
	private function setFilterValue($name,$value)
	{
		//$keys = array_keys($this->_filters[$name]['val']);
		//assert(in_array($value,$keys));		
		
		$this->_filters[$name]['curval'] = $value;
	}
	
	public function addFilter($name,$values,$label,$default_value=NULL)
	{
		$filter['val'] 		= $values;
		$filter['label'] 	= $label;
		$filter['type'] 	= self::FILTER_SELECT;
		
		$keys = array_keys($values);
		
		if ($default_value === NULL)
			$filter['defval'] = $keys[0];
		else
			$filter['defval'] = $default_value;
			
		assert(in_array($filter['defval'],$keys));
			
		$filter['curval'] = $filter['defval'];
		
		$this->_filters[$name] = $filter;
		
		// controleer of een waarde voor deze filer in de request werd meegegeven
		
		if (isset($_POST[$name]))			// get params have precedence over post params:
			$curval = $_POST[$name];
		else
			$curval = $this->_request->getParam($name,$filter['curval']);
		
		$this->setFilterValue($name,$curval);
	}
	
	public function addDateFilter($name,$label,$default_value=NULL)
	{
		$filter['label'] 	= $label;
		$filter['type'] 	= self::FILTER_DATE;
		
		if ($default_value === NULL)
			$filter['defval'] = date('d-m-Y');
		else
			$filter['defval'] = $default_value;
			
		$filter['curval'] = $filter['defval'];
		
		$this->_filters[$name] = $filter;
		
		// controleer of een waarde voor deze filer in de request werd meegegeven
		
		if (isset($_POST[$name]))			// get params have precedence over post params:
			$curval = $_POST[$name];
		else
			$curval = $this->_request->getParam($name,$filter['curval']);
		
		$this->setFilterValue($name,$curval);
	}
	
	public function getFilterValue($name){
		
		if (!isset( $this->_filters[$name]))
			throw new Exception("Er is geen filter gedefinieerd met deze ($name) naam.");
		
		return $this->_filters[$name]['curval'];
	}
	
	public function getFilters(){
		return $this->_filters;
	}
	
	public function getOrder()
	{
		$order = $this->_request->getParam('order',NULL);
		if ($order === NULL){
			$order = $this->_default_order;
			if ($order === NULL){
				
				throw new Exception("App_TableHelper: Geef dafault order ingesteld.");
			}
		}
		
		return $order;
	}
	
	public function getSort(){
		return $this->_request->getParam('sort',$this->_default_sort);
	}
	
	public function getInvSort(){		
		if ($this->getSort() == 'asc')
			return 'desc';
		else
			return 'asc';
		
	}
	
	public function postRedirectGet()
	{
		if ($this->_request->isPost())
		{
			$page 	= $this->_request->getParam('page',1);
			$order	= $this->getOrder();
			$sort	= $this->getSort();
			
			$params = array();
    		foreach($this->_filters as $name=>$filter){
    			if ($filter['defval'] != $filter['curval'])
    				$params[$name] = $filter['curval'];
    		}
    		
    		if ($page !== 1)
    			$params['page'] = $page;
    			
    		if ($sort != 'asc')
    			$params['sort'] = $sort;
    			
    		if ($order != $this->_default_order)
    			$params['order'] = $order;
    			
    		
    		$controller = $this->_request->getControllerName();
    		$action = $this->_request->getActionName();
    		$module = $this->_request->getModuleName();
    		
    		return $this->_owner->getHelper('Redirector')->gotoSimple($action,$controller,$module,$params);
    	}		
	}
	
	public function setItemcountValues($values){
		$this->_itemcount_values = $values;
	}
	
	public function getItemcountValues(){
		return $this->_itemcount_values;
	}
	
	public function getItemcount(){
		return Zend_Registry::getInstance()->session->misc->num_items_per_page;
	}
	
	public function callFormatFunction($func_name,$value){
		return $this->_owner->$func_name($value);
	}
	
	public function callViewHelper($helpername,$value){
		return $this->_owner->view->$helpername($value);
	}
}