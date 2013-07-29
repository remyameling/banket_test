<?php



// Create a handler function
function assert_handler($file, $line, $code)
{
    $msg  = "<hr>Assertion Failed: File '$file' Line '$line' Code '$code'<br /><hr />";
        
    Zend_Registry::getInstance()->logger->log('ASSERT: '.$msg, Zend_Log::EMERG);
    
    if (Zend_Registry::getInstance()->logging->assert_bail){
    	echo "<pre>";
    	debug_print_backtrace();
    	echo "</pre>";
    	die($msg);
    }
}

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initConfig(){
		$ini_files = new Zend_Config_Ini(APPLICATION_PATH.'/configs/files.ini',APPLICATION_ENV);
		$registry  = Zend_Registry::getInstance();
		
		foreach($ini_files->files as $file){
			$config = new Zend_Config_Ini(APPLICATION_PATH."/configs/".$file,APPLICATION_ENV);
			$parts  = explode(".",$file);
			$name   = $parts[0];
			$registry->set($name,$config);
		}
		
		foreach($registry->modules as $module_name)
		{
			if (isset($ini_files->module->$module_name))
			{
				foreach($ini_files->module->get($module_name)->files as $file){
					$config = new Zend_Config_Ini(APPLICATION_PATH."/".$registry->paths->module->get($module_name)."/configs/".$file,APPLICATION_ENV);
					$parts  = explode(".",$file);
					$name   = $module_name."_".$parts[0];
					$registry->set($name,$config);
				}
			}
		}

		$config = new Zend_Config_Ini(APPLICATION_PATH."/../sites/".WEBSITE."/configs/site.ini",APPLICATION_ENV);
		$registry->set("site",$config);
		
		$config = new Zend_Config_Ini(APPLICATION_PATH."/../sites/".WEBSITE."/configs/logging.ini",APPLICATION_ENV);
		$registry->set("logging",$config);
		
		$config = new Zend_Config_Ini(APPLICATION_PATH."/../sites/".WEBSITE."/configs/mail.ini",APPLICATION_ENV);
		$registry->set("mail",$config);
		
		$config = new Zend_Config_Ini(APPLICATION_PATH."/../sites/".WEBSITE."/configs/acl.ini",APPLICATION_ENV);
		$registry->set("site_acl",$config);
		
		
	}
	
	protected function _initAutoload()
    {
    	$autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'RAC_',
            'basePath'  => APPLICATION_PATH, /*LIB."RAC/",*/
        ));
        
        $autoloader->addResourceType('Component','components','Component');
        
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'App_',
            'basePath'  => APPLICATION_PATH,
        ));
        
        $autoloader->addResourceType('Models','models/','Model');
        
        // modules
        
        foreach(Zend_Registry::getInstance()->modules as $module_name){
        	
        	$autoloader = new Zend_Application_Module_Autoloader(array(
	            'namespace' => ucwords($module_name).'_',
	            'basePath'  => APPLICATION_PATH.'/modules/'.$module_name,
        	));        	
        }         

        // others
        
       $autoloader = Zend_Loader_Autoloader::getInstance();
       $autoloader->registerNamespace('App_');
    }
    
    
    protected function _initWebsite(){
    	// Define website: dit moet als environment variable in de .htaccess file staan, 
    	// of als parameter worden meegegeven
    	
    	$website = NULL;
    	    	
    	if (!isset($_GET['website']))
    		$website = getenv('WEBSITE');
    	else
    		$website = $_GET['website'];
    		
    	defined('WEBSITE')
    		|| define('WEBSITE', $website);
    		
    	if (WEBSITE === NULL)
    		die("Bootstrap: WEBSITE not defined.");  
    }
    
	protected function _initDecorators()
    {
		$form = new Zend_Form();
		$form->addElementPrefixPath('My_Decorator','My/Decorator/','decorator');   
    }
    
    protected function _initFront()
    {
    	$front = Zend_Controller_Front::getInstance();
    	
    	foreach(Zend_Registry::getInstance()->modules as $module_name){
    		$modules[$module_name] = APPLICATION_PATH . '/modules/'.$module_name.'/controllers';
    	}
    	
    	$front->setControllerDirectory($modules);
    	
    	$front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
			'module'     => 'default',
    	    'controller' => 'errors',
    		'action'     => 'error')));
    	
    	
    	
    }

	protected function _initDoctype()
    {
		$this->bootstrap('view');
        $view = $this->getResource('view');		
        $view->doctype('XHTML1_STRICT');
    }
	
	public function _initLog(){
		
		
		$logger = new Zend_Log();
		
		if (Zend_Registry::getInstance()->logging->logtofirebug)
		{		
			
			$writer1 = new Zend_Log_Writer_Firebug();

			$logger->addWriter($writer1);					
			
			$filter1 = new Zend_Log_Filter_Priority((int)Zend_Registry::getInstance()->logging->firebuglevel);
			$writer1->addFilter($filter1);
		}
		
		if (Zend_Registry::getInstance()->logging->logtofile)
		{
			$writer2 = new Zend_Log_Writer_Stream(Zend_Registry::getInstance()->logging->logfile);
			$logger->addWriter($writer2);			
			
			$filter2 = new Zend_Log_Filter_Priority((int)Zend_Registry::getInstance()->logging->logfilelevel);
			$writer2->addFilter($filter2);
		}
		
		Zend_Registry::getInstance()->logger = $logger;
		
		Zend_Registry::getInstance()->logger->log('---> Log initialized environment = '.
												  APPLICATION_ENV.', level = '.
												  Zend_Registry::getInstance()->logging->firebuglevel.
												  ' <---', Zend_Log::NOTICE);
	
	}
	
	protected function _initScriptPath(){
		
    	$view = $this->getResource('view');
    	$view->addScriptPath(realpath(APPLICATION_PATH."/".Zend_Registry::getInstance()->consts->path_template));
    }    
    
	protected function _initDatabase(){
		$registry  = Zend_Registry::getInstance();
		
		$dbAdapter = Zend_Db::factory(Zend_Registry::getInstance()->site->resources->db);
		
		if (Zend_Registry::getInstance()->logging->profile){		
			
			
			
			$profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
			$profiler->setEnabled(true);
	
			$dbAdapter->setProfiler($profiler);
	 
			$dbAdapter->getConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
		}
			
		
		// DATABASE TABLE SETUP - Setup the Database Table Adapter
		// Since our application will be utilizing the Zend_Db_Table component, we need 
		// to give it a default adapter that all table objects will be able to utilize 
		// when sending queries to the db.
		Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
		$registry->dbAdapter     = $dbAdapter;
		
		
	}
	
	
	protected function _initDbSettings(){
		// add a registry configuration with database settings 
		
		$setmdl 	= new App_Model_Setting;
		
		$settings	= $setmdl->fetchEntries();
		
		$data       = array();	
		
		if ($settings !== NULL){
			foreach($settings as $setting){
				$data[$setting['setting_name']] = $setting['setting_value'];
			}
		}	
		Zend_Registry::getInstance()->set("db_settings",new Zend_Config($data));

		
	}
	
	protected function _initModels()
	{
		
		// registreer Eniac / MySql modellen
		
		if (APPLICATION_ENV == 'development')
		{
			App_ModelFactory::register("Planning",new App_Model_PlanningMySql());
			App_ModelFactory::register("Pallets",new App_Model_PalletsMySql());
		}
		else
		{
			App_ModelFactory::register("Planning",new App_Model_PlanningEniac());
			App_ModelFactory::register("Pallets",new App_Model_PalletsEniac());
		}
	}	
	
	protected function _initRoutes()
	{
		$router = Zend_Controller_Front::getInstance()->getRouter();		
		
		$routes = Zend_Registry::getInstance()->routes;
		
		//print_r($routes->toArray());
		//die();
		
		if ($routes->route !== NULL){
			foreach($routes->route as $route_def)
			{
				$route_info['module'] 	  = isset($route_def->module) ? $route_def->module : $routes->default->module;
				$route_info['controller'] = isset($route_def->controller) ? $route_def->controller : $routes->default->controller;
				$route_info['action']     = isset($route_def->action) ? $route_def->action : $routes->default->action;			
				$params     			  = isset($route_def->params) ? $route_def->params->toArray() : array();
				
				$route_info = array_merge($route_info,$params);
				$route      = new Zend_Controller_Router_Route($route_def->path,$route_info);
				$router->addRoute($route_def->name,$route);			
			}
		}		
    }
    
	
	
	protected function _initAuthorization(){
		
		require_once realpath(APPLICATION_PATH.Zend_Registry::getInstance()->paths->components)."/Acl.php";
		
		$auth 		= App_Auth_Auth::getInstance();
		$acl		= new RACCMS_Component_Acl($auth,
											   Zend_Registry::getInstance()->roles,
											   Zend_Registry::getInstance()->resources,
											   Zend_Registry::getInstance()->permissions,
											   Zend_Registry::getInstance()->site_acl);
				
		$plugin 	= new RAC_Component_Pluginauth($auth, $acl);
		
		$plugin->set401Page('denied', 'errors', 'default'); 
		$plugin->set404Page('nopage', 'errors', 'default');
		
		Zend_Registry::getInstance()->acl = $acl;

		Zend_Controller_Front::getInstance()->registerPlugin($plugin);		
	}
	
	
	protected function _initAssert(){
		
		assert_options(ASSERT_ACTIVE,1);
		
		if (Zend_Registry::getInstance()->logging->assert_bail){
			assert_options(ASSERT_BAIL,true);
		}
		else
			assert_options(ASSERT_BAIL,false);
			
		// Set up the callback
		assert_options(ASSERT_CALLBACK, 'assert_handler');		
	}
	
	public function _initSession(){		
		
		$session = new Zend_Session_Namespace('Default');
		Zend_Registry::getInstance()->session = $session;   		   
	}
	
	public function _initTempDir(){
		
		$_ENV['TMPDIR'] = APPLICATION_PATH.'/../temp';
	}
	
	public function _initValidators(){
		   
			$translator = new Zend_Translate('array',LIB.ZEND.'/resources/languages/',
											 'nl',array('scan' => Zend_Translate::LOCALE_DIRECTORY));
			
			Zend_Validate_Abstract::setDefaultTranslator($translator);
	}
	
	
	
	public function _initViewHelpers(){
		   $view = new Zend_View();
   		   $view->addHelperPath(APPLICATION_PATH.'/views/helpers', 'App_View_Helper');
   		   $view->addHelperPath(APPLICATION_PATH.'/modules/default/views/helpers', 'Default_View_Helper');
   		   $view->addHelperPath(APPLICATION_PATH.'/modules/account/views/helpers', 'Account_View_Helper');
   		   $view->addHelperPath(APPLICATION_PATH.'/modules/catalog/views/helpers', 'Catalog_View_Helper');
   		   $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
   		   $viewRenderer->setView($view);
   		   Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
	}	
}