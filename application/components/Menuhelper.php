<?php

class RACCMS_Menuhelper
{
	private function _getAriclePages($weblog_id,$weblogname,$addsitemap_data){
    	
    	$a_mdl   = new App_Model_Article();
    	$entries = $a_mdl->fetchByWeblog($weblog_id);
    	
    	$pages = array();
    	foreach($entries as $entry){
    		
    		$params = array('weblog'=>$weblogname,'article'=>$entry['article_uniquename']);
    		
    		$pages[] = new Zend_Navigation_Page_Mvc(array(
			    			'label'		 	=> $entry['article_title'],
			    			'title'		 	=> $entry['article_title'],
			    			'action'     	=> 'view',
			    			'controller' 	=> 'article',
			    			'module'     	=> 'default',
			    			'route'		 	=> 'wla_'.$weblogname,
			    			'reset_params' 	=> true,
			    			'resource'		=> 'weblog_'.$weblogname,
			    			'privilege'		=> 'read',
			    			'pages'			=> array(),
			    			'params'     	=> $params,
    						'lastmod'		=> $entry['changed'],
    						'priority'		=> Zend_Registry::getInstance()->consts->sitemap->priority->article));
    	}
    	
    	return $pages;
    }
    
	private function _getWeblogPagination($weblog_id,$weblogname,$addsitemap_data){
    	
    	$a_mdl   	= new App_Model_Article();
    	$entries 	= $a_mdl->fetchByWeblog($weblog_id);
    	$num_pages	= (integer) ceil(count($entries)/ Zend_Registry::getInstance()->site->pagination->numitemsperpage);
    	$pages      = array();
    	
    	if ($num_pages > 1)
    	{
    		for($page=1;$page<=$num_pages;$page++){
	    		
	    		$params = array('weblog'=>$weblogname,'page'=>$page);
	    		
	    		$pages[] = new Zend_Navigation_Page_Mvc(array(
				    			'label'		 	=> "pagina ".$page,
				    			'title'		 	=> "pagina ".$page,
				    			'action'     	=> 'indexnew',
				    			'controller' 	=> 'weblog',
				    			'module'     	=> 'default',
				    			'route'		 	=> 'wl_'.$weblogname,
				    			'reset_params' 	=> true,
				    			'resource'		=> Zend_Registry::getInstance()->acl->getResourceString("weblog",$weblogname),
				    			'privilege'		=> 'read',
				    			'pages'			=> array(),
				    			'params'     	=> $params,
	    						'lastmod'		=> NULL,
	    						'priority'		=> Zend_Registry::getInstance()->consts->sitemap->priority->weblog));
	    	}
    	}
    	
    	return $pages;
    }
    
	private function _getMenuName($id){
    	$m_mdl = new App_Model_Menu();
    	$entry = $m_mdl->fetchEntry($id);
    	assert($entry !== NULL);
    	return $entry['menu_name'];
    }
	
	private function _getUniqueFormName($form_id){
    	$f_mdl = new App_Model_Form();
    	$entry = $f_mdl->fetchEntry($form_id);
    	if ($entry == NULL)
    		throw new Exception("RACCMS_Menuhelper::_getUniqueFormName($form_id): formulier niet gevonden.");
    	return $entry['form_uniquename'];
    }
    
	private function _getUniqueCalendarName($calendar_id){
    	$c_mdl = new App_Model_Calendar();
    	$entry = $c_mdl->fetchEntry($calendar_id);
    	if ($entry == NULL)
    		throw new Exception("RACCMS_Menuhelper::_getUniqueCalendarName($calendar_id): kalender niet gevonden.");
    	return $entry['calendar_uniquename'];
    }
	
	private function _getUniqueArticleName($id){
		
		assert($id !== NULL);
		
    	$a_mdl 	= new App_Model_Article();
    	$entry  = $a_mdl->fetchEntry($id);
    	if ($entry == NULL){
    		assert(false);
    		die("RACCMS_Menuhelper::_getUniqueArticleName($id): artikel niet gevonden.");
    		throw new Exception("RACCMS_Menuhelper::_getUniqueArticleName($id): artikel niet gevonden.");
    	}
    	return $entry['article_uniquename'];
    }
	
	private function _getUniqueWeblogName($weblog_id){
    	$wl_mdl = new App_Model_Weblog();
    	$entry  = $wl_mdl->fetchEntry($weblog_id);
    	if ($entry == NULL)
    		throw new Exception("RACCMS_Menuhelper::_getUniqueWeblogName($weblog_id): weblog niet gevonden.");
    	return $entry['weblog_uniquename'];
    }
    
	private function _getDefaultArticleName($weblogname){
    	$wl_mdl = new App_Model_Weblog();
    	$entry  = $wl_mdl->fetchDefaultArticle($weblogname);
    	
    	if ($entry !== NULL)
    		return $entry['article_uniquename'];
    	else
    		return "";
    }
	
	protected function _getDefaultWeblogData(){
    	
		$wl_mdl = new App_Model_Weblog();
		
    	$weblog_data   = $wl_mdl->fetchDefault();  
    	assert($weblog_data !== NULL);  	
    	return $weblog_data;
    }
    
    private function _getLastModified($weblog_name,$article_name=NULL,$article_data=NULL){
    	
    	if ($article_data === NULL){
    		assert($article_name !== NULL);
    		
    		$wlmdl = new App_Model_Weblog();
    		$article_data = $wlmdl->fetchArticle($weblog_name,$article_name);
    	}
    	
    	return $article_data['changed'];
    	
    }
    
    private function getDefaultData($entry,$controller,$action,$module='default'){
    	
    	$data['controller'] 	= $controller;
    	$data['action'] 		= $action;
    	$data['module']			= $module;
    	$data['label'] 	    	= $entry['menuitem_label'];
    	$data['title'] 	    	= $entry['menuitem_title'];
    	$data['route']	    	= 'default';
    	$data['resource']   	= NULL;
    	$data['privilege']		= "read";    	
    	$data['lastmod']    	= NULL;
    	$data['priority']		= Zend_Registry::getInstance()->consts->sitemap->priority->default;
    	$data['params']			= NULL;
    	$data['reset_params']	= true;

    	return $data;
    }
    
    private function getPageHomepageData($entry){
    	// returns 
    	
    	$data 				= $this->getDefaultData($entry,'index','index');    	
    	$def_wl     		= $this->_getDefaultWeblogData();
    	$data['resource']   = Zend_Registry::getInstance()->acl->getResourceString("weblog",$def_wl['weblog_uniquename']);
    			
    	$lastmod    		= new Zend_Date();    			
    	$articleModel 		= new App_Model_Article();
	    $articles     		= $articleModel->fetchByWeblog($def_wl['id']);
	    
	    if (isset($articles[0])){
	    	$lastmodified = $this->_getLastModified($def_wl['weblog_uniquename'],$articles[0]['article_uniquename'],$articles[0]);
	    	$lastmod->set($lastmodified);
	    }
	    
	    $data['lastmod']    = $lastmod;
	    
	    return $data;    
    }
    
	private function getPagePlaceholder($entry){
    	// returns 
    	
    	$data['label'] = $entry['menuitem_label'];
    	$data['title'] = $entry['menuitem_title'];
    	$data['uri']   = "#";
    	
    	return $data;
    }
    
    private function getPageLinkToWeblog($entry){
    	
    	$data 				= $this->getDefaultData($entry,'weblog','indexnew');
    	$acl                = Zend_Registry::getInstance()->acl;
    	$weblogname       	= $this->_getUniqueWeblogName($entry['menuitem_param1']);
    	
    	$data['resource'] 	= $acl->getResourceString("weblog",$weblogname);
    	
    	switch($entry['menuitem_param2'])
    	{
    		case Zend_Registry::getInstance()->consts->menuitem->showarticlesinmenu->pages:
    			$data['route']    	= 'wl_'.$weblogname;
    			$data['params']	  	= array('weblog' => $weblogname,'page'=>1);
    			$data['pages']  	= $this->_getWeblogPagination($entry['menuitem_param1'],$weblogname,true);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitem->showarticlesinmenu->article:
    			
    			$data['route']    	= 'wla_'.$weblogname;
    			$data['params']	  	= array('weblog' => $weblogname,'article'=>$this->_getDefaultArticleName($weblogname));
    			$data['pages']  	= $this->_getAriclePages($entry['menuitem_param1'],$weblogname,true);
    			break;
    		default:
    			$data['route']    	= 'wl_'.$weblogname;
    			$data['params']	  	= array('weblog' => $weblogname,'page'=>1);
    			$data['pages']  	= array();
    			break;
    				
    	}
    			
    	$lastmod    	= new Zend_Date();    			
    	$wlmdl		 	= new App_Model_Weblog();
	    $wl_data     	= $wlmdl->fetchEntryByUniqueName($weblogname);
	    if ($wl_data !== NULL){
	    	$lastmodified = $wl_data['wlchanged'];
	    	$lastmod->set($lastmodified);
	    }
	    
	    $data['lastmod'] 	= $lastmod;	    
	    $data['priority']	= Zend_Registry::getInstance()->consts->sitemap->priority->weblog;
    	  	
	    
	    return $data;    
    }
    
    private function getPageLinkToArticle($entry){
    	
    	$data = $this->getDefaultData($entry,'article','view');
    	
    	$acl  = Zend_Registry::getInstance()->acl;
    
    	$weblogname  = $this->_getUniqueWeblogName($entry['menuitem_param1']);
    	
    	if ($entry['menuitem_param2'] === NULL){
    		
    		$wlMdl 		 = new App_Model_Weblog();
    		$articledata = $wlMdl->fetchDefaultArticle($weblogname);
    		$article_id  = $articledata['id'];
    		
    	}
    	else
    		$article_id = $entry['menuitem_param2'];
    	
    	$articlename = $this->_getUniqueArticleName($article_id);   	
    	
    	$data['params']      = array('weblog' => $weblogname,'article'=>$articlename);
    	$data['route']       = 'wla_'.$weblogname;
    	$data['resource']    = $acl->getResourceString("weblog",$weblogname);
    	
    	$lastmod      = new Zend_Date();    			
    	$lastmodified = $this->_getLastModified($weblogname,$articlename,NULL);
	    $lastmod->set($lastmodified);

	    $data['lastmod'] 	= $lastmod;
	    $data['priority']	= Zend_Registry::getInstance()->consts->sitemap->priority->article;
	    
	    return $data;
    }
    
    private function getPageLinkToLogin($entry){
    
    	$data 	= $this->getDefaultData($entry,'login','index','account');
    	$acl    = Zend_Registry::getInstance()->acl;
    		
    	$data['resource'] = $acl->getResourceString("account","login");
    	
    	if (!App_Auth_Auth::getInstance()->hasIdentity()){	// indien nog niet ingelogd
    	
    		$data['action'] 	= 'index';
    		$data['label']		= $entry['menuitem_label'];    				
    		$data['privilege']	= "index";
    	}
    	else{
    		
    		$data['action']		= 'logout'; 
    		$data['privilege']	= 'logout'; 				

    		if ($entry['menuitem_param1'] == 1)	// wel of niet username tonen in label
    			$data['label']  	=  App_Auth_Auth::getInstance()->getIdentityAlias()." (uitloggen)";
    		else
    			$data['label']  	= "uitloggen";   				
    	}
    	
    	return $data;
    }
    
    private function getPageLinkToForm($entry){

    	$data 	= $this->getDefaultData($entry,'form','index');
    	
    	$form_name      = $this->_getUniqueFormName($entry['menuitem_param1']);   
    	 			
    	$data['resource']  = 'form_'.$form_name;
    	$data['privilege'] = 'read';
    	$data['params']    = array('form' => $form_name);
    	$data['route']     = 'frm_'.$form_name;  					
    	
    	return $data;
    }
    
	private function getPageLinkToCalendar($entry){

    	$data 			= $this->getDefaultData($entry,'calendar','month');
    	
    	$calendar_name  = $this->_getUniqueCalendarName($entry['menuitem_param1']);   
    	 			
    	$data['resource']  = 'calendar_'.$calendar_name;
    	$data['privilege'] = 'read';
    	$data['params']    = array('name' => $calendar_name,'y'=>0,'m'=>0);
    	$data['route']     = 'calendarmonth';  					
    	
    	return $data;
    }
    
    private function getPageLinkToCms($entry){

    	$data 	= $this->getDefaultData($entry,'index','index','admin');
    
    	$data['resource']  = 'admin_index';
    	$data['privilege'] = 'index';	
    	 
    	return $data;    	
    }
    
    private function getPageLinkToSitemap($entry){
    	
    	$data 	= $this->getDefaultData($entry,'sitemap','index');
    
    	$data['resource']  = 'default_sitemap';
    	$data['privilege'] = 'index';	
    	 
    	return $data;			
    }
    
	private function getPageLinkToAccount($entry){
    	
    	$data 	= $this->getDefaultData($entry,'index','index','account');
    
    	$data['resource']  = 'account_index';
    	$data['privilege'] = 'index';	
    	 
    	return $data;			
    }
    
	private function getPageProductIndex($entry){
    	
    	$data 	= array();
    	
    	$data['controller'] 	= 'product';
    	$data['action'] 		= 'index';
    	$data['module']			= 'catalog';
    	$data['label'] 	    	= $entry['category_name'];
    	$data['title'] 	    	= $entry['category_name'];
    	$data['route']	    	= 'productindex';
    	$data['resource']   	= NULL;
    	$data['privilege']		= NULL;    	
    	$data['lastmod']    	= NULL;
    	$data['priority']		= Zend_Registry::getInstance()->consts->sitemap->priority->default;
    	$data['params']			= array('category'=>$entry['category_uniquename'],'page'=>1);
    	$data['reset_params']	= true;
    	 
    	return $data;			
    }
    
    private function getPage($type,$data)
    {
    	
    	switch($type)
    	{
    		case Zend_Registry::getInstance()->consts->menuitemtype->placeholder:
    			
    			$page = new Zend_Navigation_Page_Uri($data);
    			$page->setUri($data['uri']);
    			return $page;
    		default:
    			return new Zend_Navigation_Page_Mvc($data);
    	}
    }
    
    private function getNavigationPageNew($entry,$addweblogarticles){
    	
    	$data = array();
    	switch($entry['menuitemtype_id']){
    		case Zend_Registry::getInstance()->consts->menuitemtype->homepage:
    			$data = $this->getPageHomepageData($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->placeholder:
    			$data = $this->getPagePlaceholder($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktoweblog:
    			$data = $this->getPageLinkToWeblog($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktoarticle:	
    			$data = $this->getPageLinkToArticle($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktologinpage:
    			$data = $this->getPageLinkToLogin($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktoform:
    			$data = $this->getPageLinkToForm($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktocms:
    			$data = $this->getPageLinkToCms($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktositemap:
    			$data = $this->getPageLinkToSitemap($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktoaccount:
    			$data = $this->getPageLinkToAccount($entry);
    			break;
    		case Zend_Registry::getInstance()->consts->menuitemtype->linktocalendar:
    			$data = $this->getPageLinkToCalendar($entry);
    			break;
    	}
    	
    	// add child pages    	   	
    	
    	if ($entry['childs'] !== NULL){
    		foreach($entry['childs'] as $child)
    			$pages[] = $this->getNavigationPageNew($child,$addweblogarticles);
    			
    		if (isset($data['pages']))
    			$data['pages'] = array_merge($pages,$data['pages']);
    		else 	
    			$data['pages'] = $pages;
    	}
    	
    	
    	
    	$page = $this->getPage($entry['menuitemtype_id'],$data);
    	   	
    	return $page;   	
    }
    
	private function getCategoryNavigationPage($entry){
    	
    	$data = array();
    	$data = $this->getPageProductIndex($entry);
    	
    	
    	// add child pages    	   	
    	
    	if ($entry['childs'] !== NULL){
    		foreach($entry['childs'] as $child)
    			$pages[] = $this->getCategoryNavigationPage($child);
    			
    		if (isset($data['pages']))
    			$data['pages'] = array_merge($pages,$data['pages']);
    		else 	
    			$data['pages'] = $pages;
    			
    		//$data['uri'] = "#";										// indien deze categorie, childs bevat, dan    		
    		//$page 		 = new Zend_Navigation_Page_Uri($data);		// geen navigatie naar de parent categorie
    																// (vooralsnog hebben parent categorieen geen produkten)
    		$page = new Zend_Navigation_Page_Mvc($data);
    		
    	}
    	else
    		$page = new Zend_Navigation_Page_Mvc($data);    		
    	   	
    	return $page;   	
    }
    
    
    
	
	public function getMenu($menu_name,$addweblogarticles=false,$add_sitemapdata=false){
    	
    	assert($menu_name !== NULL);
    	$pages = array();
    	
    	$model = new App_Model_Menu();
    	$data  = $model->fetchEntryByUniqueName($menu_name);
    	
    	if ($data === NULL){
    		die("menu : $menu_name not found");
    		throw new Exception("MenuController::getMenu($menu_name): menu niet gevonden.");
    	}
    	
    	assert($data !== NULL);
    	assert(isset($data['id']));
    	
    	$model   = new App_Model_Menuitem();
    	$entries = $model->fetchItems($data['id']);
    	
    	foreach($entries as $entry){
    		$pages[] = $this->getNavigationPageNew($entry,$addweblogarticles);
    	}
    	    	
    	$container 	= new Zend_Navigation();
        $container->addPages($pages);
        
        return $container;
    }
    
	public function getCategoryNavigation($catalog_name,$add_sitemapdata=false){
    	
    	assert($catalog_name !== NULL);
    	$pages = array();
    	
    	$model = new App_Model_Catalog();
    	$data  = $model->fetchByName($catalog_name);
    	
    	if ($data === NULL){
    		die("catalog : $catalog_name not found");
    		throw new Exception("MenuController::getCategoryNavigation($catalog_name): catalog niet gevonden.");
    	}
    	
    	assert($data !== NULL);
    	assert(isset($data['id']));
    	
    	$model   = new App_Model_Category();
    	$entries = $model->fetchItems($data['id']);
    	
    	foreach($entries as $entry){
    		$pages[] = $this->getCategoryNavigationPage($entry);
    	}
    	    	
    	$container 	= new Zend_Navigation();
        $container->addPages($pages);
        
        return $container;
    }
	
}