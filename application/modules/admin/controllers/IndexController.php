<?php

require_once "BaseController.php";

class Admin_IndexController extends Admin_BaseController
{
	
	private function checkIfWritable($directory){
		
		if ((file_exists($directory)) && (is_writable($directory)))
			return true;
		else	
			return false;
		
	}
	
	private function checkSystem(){
		
		$msg = NULL;
		
		// check if directories are present and writable
		
		if (!$this->checkIfWritable(APPLICATION_PATH.'/../temp'))
			$msg[] = "Temp directory (".APPLICATION_PATH.'/../temp'.") niet aanwezig of geen schrijfrechten";
		if (!$this->checkIfWritable(Zend_Registry::getInstance()->logging->logfile))
			$msg[] = "Logging directory (".Zend_Registry::getInstance()->logging->logfile.") niet aanwezig of geen schrijfrechten";
		if (!$this->checkIfWritable($this->_getUploadDirectory()))
			$msg[] = "Upload directory (".$this->_getUploadDirectory().") niet aanwezig of geen schrijfrechten";	
		if (!$this->checkIfWritable($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_cache))
			$msg[] = "Cache directory (".$this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_cache.") niet aanwezig of geen schrijfrechten";
		if (!$this->checkIfWritable($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup))
			$msg[] = "Backup directory (".$this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup.") niet aanwezig of geen schrijfrechten";
			
		// check DB version
		
		if (Zend_Registry::getInstance()->consts->db_version != Zend_Registry::getInstance()->db_settings->db_version)
			$msg[] = "Database versie (".
					 Zend_Registry::getInstance()->db_settings->db_version.
					 ") niet gelijk aan actuele versie (".
					 Zend_Registry::getInstance()->consts->db_version.")";
		
		
		return $msg;
	}
	
	private function getWidgetSystemmessages($widget_config){
		
		$widget['title'] = $widget_config->title;
		
		$rows = array();
		$rows[0]['cols'][0]	 = "&nbsp;";
		$rows[0]['cols'][1]	 = "&nbsp;";
		$rows[1]['cols'][0]    = "Applicatie";
		$rows[1]['cols'][1]	 = Zend_Registry::getInstance()->consts->application_name;
		$rows[2]['cols'][0]    = "Versie";
		$rows[2]['cols'][1]	 = Zend_Registry::getInstance()->consts->application_version;
		$rows[3]['cols'][0]    = "Licentie";
		$rows[3]['cols'][1]	 = Zend_Registry::getInstance()->site->licence->type;
		$rows[4]['cols'][0]    = "Geldig tot";
		$rows[4]['cols'][1]	 = Zend_Registry::getInstance()->site->licence->until;
		$rows[5]['cols'][0]    = "Houder";
		$rows[5]['cols'][1]	 = Zend_Registry::getInstance()->site->licence->holder;
				
		$widget['rows']  = $rows;
		$widget['type'] = 'table';
		
		return $widget;
	}
	
	private function getWidgetActiveusers($widget_config){
		
		$widget['title'] = $widget_config->title;
		
		$umdl = new App_Model_User();
		$users = $umdl->fetchMostActive($widget_config->count,2);
		
		$rows = array();
		$row  = array();
		
		$row['cols'][0] = "User naam";
		$row['cols'][1] = "Laatste login";
		$row['cols'][2] = "Aantal logins";
		$rows[] = $row;
		
		if (count($users) > 0)
		{
			foreach($users as $user){
				$row['cols'][0] = '<a href="/admin/user/edit/id/'.$user['id'].'">'.$user['user_alias'].'</a>';
				$row['cols'][1] = $user['user_lastlogin'];
				$row['cols'][2] = $user['user_numlogins'];
				$rows[] = $row;
			}
		}
		
		$widget['rows']  = $rows;
		$widget['type'] = 'table';
		
		return $widget;
	}
	
	private function getWidgetRecentArticles($widget_config){
		
		$widget['title'] = $widget_config->title;
		
		$amdl 	  = new App_Model_Article();
		$articles = $amdl->fetchRecent($widget_config->count);
		
		$rows = array();
		$row  = array();
		
		$row['cols'][0] = "Titel";
		$row['cols'][1] = "Gewijzigd";
		$row['cols'][2] = "Door";
		$rows[] = $row;
		
		foreach($articles as $article){
			$row['cols'][0] = '<a href="/admin/article/edit/id/'.$article['id'].'">'.substr($article['article_title'],0,20).'</a>';
			$row['cols'][1] = $article['changed'];
			$row['cols'][2] = $article['changedby'];
			$rows[] = $row;
		}
		
		$widget['rows']  = $rows;
		$widget['type'] = 'table';
		
		return $widget;
	}
	
	private function getWidgetRecentorders($widget_config){
		
		$widget['title'] = $widget_config->title;
		
		$pmdl 	  = new App_Model_Order();
		$orders   = $pmdl->fetchRecent($widget_config->count);
		
		$rows = array();
		$row  = array();
		
		$row['cols'][0] = "Bestelnummer";
		$row['cols'][1] = "Status";
		$row['cols'][2] = "Door";
		$rows[] = $row;
		
		foreach($orders as $order){
			$row['cols'][0] = '<a href="/admin/order/vieworder/id/'.$order['id'].'">'.substr($order['order_number'],0,20).'</a>';
			$row['cols'][1] = '<a href="/admin/order/vieworder/id/'.$order['id'].'">'.$order['state'].'</a>';
			$row['cols'][2] = '<a href="/admin/customer/edit/id/'.$order['customer_id'].'">'.$order['customer_fullname'].'</a>';
			$rows[] = $row;
		}
		
		$widget['rows']  = $rows;
		$widget['type'] = 'table';
		
		return $widget;
	}
	
	private function getWidgetRecentProducts($widget_config){
		
		$widget['title'] = $widget_config->title;
		
		$pmdl 	  = new App_Model_Product();
		$products = $pmdl->fetchRecent($widget_config->count);
		
		$rows = array();
		$row  = array();
		
		$row['cols'][0] = "Naam";
		$row['cols'][1] = "Gewijzigd";
		$row['cols'][2] = "Door";
		$rows[] = $row;
		
		foreach($products as $product){
			$row['cols'][0] = '<a href="/admin/product/edit/id/'.$product['id'].'">'.substr($product['product_name'],0,20).'</a>';
			$row['cols'][1] = $product['changed'];
			$row['cols'][2] = $product['changedby'];
			$rows[] = $row;
		}
		
		$widget['rows']  = $rows;
		$widget['type'] = 'table';
		
		return $widget;
	}
	
	private function getWidgetRecentComments($widget_config){
		
		$widget['title'] = $widget_config->title;
		
		$cmdl 	  = new App_Model_Comment();
		$comments = $cmdl->fetchUnpublishedComments($widget_config->count);
		
		$rows = array();
		$row  = array();
		
		$row['cols'][0] = "Akties";
		$row['cols'][1] = "Auteur";
		$row['cols'][2] = "E-mail adres";
		$rows[] = $row;
		
		foreach($comments as $comment){
			$row['cols'][0] = '<a href="/admin/comment/edit/id/'.$comment['id'].'">bekijk</a> | <a href="/admin/comment/delete/id/'.$comment['id'].'">verwijder</a> | <a href="/admin/comment/publish/id/'.$comment['id'].'">plaats</a>';
			$row['cols'][1] = $comment['comment_author'];
			$row['cols'][2] = $comment['comment_email'];
			$rows[] = $row;
		}
		
		$widget['rows']  = $rows;
		$widget['type'] = 'table';
		
		return $widget;
	}
	
	private function getWidgets(){
		
		return NULL;
		foreach(Zend_Registry::getInstance()->site_widgets->widget as $widget_name=>$widget_config){						
			if (Zend_Registry::getInstance()->site_widgets->widget->get($widget_name)->display)			
				$methodName = 'getWidget'.$widget_name;			
				$widgets[$widget_name] = $this->$methodName($widget_config);
		}
		return $widgets;		
	}

    public function indexAction()
    {
    	if (App_Auth_Auth::getInstance()->hasIdentity())
    	{
    		$request = $this->getRequest();		
			$this->view->request = $request;
			
			$this->view->msg = $this->checkSystem();
			if ($this->view->msg === NULL)
				$this->view->msg[] = "Er zijn geen meldingen";
				
			$this->view->widgets = $this->getWidgets();
			
			
    	} else {
    		$this->_helper->getHelper('Redirector')->gotoSimple('index','login','admin',array(''));
    	}
    }
    
    private function sanitize($input) {
		return htmlentities(strip_tags( $input ));
	}
    
    
    private function addArticle($index,$id){
    	
    	$model = new App_Model_Article();
    	$data  = $model->fetchEntry($id);
    	
    	if (isset($data['id'])){
    		
    		$w_mdl  = new App_Model_Weblog();
    		$wdata  = $w_mdl->fetchEntry($data['weblog_id']);
    		
    		$doc = new Zend_Search_Lucene_Document();
    		
    		$options = array('module'=>'default','controller'=>'article','action'=>'view',
    						 'article'=>$data['article_uniquename'],'weblog'=>$wdata['weblog_uniquename']);
    		
    		$helper = $this->_helper->url;
    		
    		
    		$url = $helper->url($options,'wla_'.$wdata['weblog_uniquename'],true);
    		
    		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',$this->sanitize($url)));
    		$doc->addField(Zend_Search_Lucene_Field::Text('title',$this->sanitize($data['article_title'])));
    		$doc->addField(Zend_Search_Lucene_Field::Text('article_uniquename',$this->sanitize($data['article_uniquename'])));
    		$doc->addField(Zend_Search_Lucene_Field::Text('weblog_uniquename',$this->sanitize($wdata['weblog_uniquename'])));  	
    		$doc->addField(Zend_Search_Lucene_Field::Unstored('summary',$this->sanitize($data['article_summary'])));
    		$doc->addField(Zend_Search_Lucene_Field::Unstored('content',$this->sanitize($data['article_content'])));
    		
    		$index->addDocument($doc);
    	}
    }
    
    public function searchAction(){
    	
    	$request 				= $this->getRequest();	
    	$create					= $request->getParam('create','false');
    	
    	$directory				= $this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_search_lucene;
    	    	
    	if ($create == 'true')
    		$index = Zend_Search_Lucene::create($directory);
    	else
    		$index = Zend_Search_Lucene::open($directory);
    		
    	// add all articles to index
    	
    	$model = new App_Model_Article();
    	$articles = $model->fetchEntries();
    	foreach($articles as $article)
    		$this->addArticle($index,$article['id']);
    	
    	$index->commit();
		$this->view->num_docs = $index->count()." Documents indexed.\n";
    }
    
	public function articlesAction()
    {
    	$request 				= $this->getRequest();	
		$this->view->request 	= $request;
		$order					= $request->getParam('order','article_name');
		$sort					= $request->getParam('sort','asc');
			
    	if (App_Auth_Auth::getInstance()->hasIdentity())
    	{
    		$article_Mdl 			= new App_Model_Article();
    		$this->view->articles 	= $article_Mdl->fetchEntries($order,$sort,array(array('table'=>'weblog','field'=>'weblog_id','joinfields'=>array('weblog_name'=>'weblog_name'))));
    		
    		
			
			
			
    	} else {
    		$this->_helper->getHelper('Redirector')->gotoSimple('index','login','admin',array(''));
    	}
    }
    
    private function repairCategorySort($category,$catalog_id,$bRepair)
    {
    	$category_mdl = new App_Model_Category();
    	$num_repaired = 0;
    	
    	if ($category['category_sortkey'] == 0)
    	{
    		echo "Categorie ".$category['category_name']." bevat foutieve sortering.";
    		$num_repaired++;
    		
    		
    		if ($bRepair)
    		{
    			$next = $category_mdl->nextSortValue('category_sortkey','category_parent_id',$category['category_parent_id'],'catalog_id',$catalog_id);
    			
    			$category_mdl->update($category['id'],array('category_sortkey'=>$next));
    			
    			echo "sortkey => $next<br />";
    		}
    		else
    			echo "<br />";
    		
    		$childs = $category_mdl->fetchChilds($category['id']);
    		foreach($childs as $child)
    			$num_repaired = $num_repaired+$this->repairCategorySort($child,$catalog_id,$bRepair);
    	}	
    	
    	return $num_repaired;
    }
    
	private function repairProductSort($category,$bRepair)
    {
    	$product_mdl = new App_Model_Product();
    	$num_repaired = 0;
    	
    	$products = $product_mdl->fetchByCategoryName($category['category_uniquename']);
    	foreach($products as $product){    		
    		if ($product['product_sortkey'] == 0)
    		{
    			$num_repaired++;
    			echo "Product ".$product['product_name']." bevat foutieve sortering.";
    			
    			if ($bRepair)
    			{
    				$next = $product_mdl->nextSortValue('product_sortkey','category_id',$category['id']);
    				$product_mdl->update($product['id'],array('product_sortkey'=>$next));
    				echo "sortkey => $next<br />";
    			}
    			else
    				echo "<br />";
    		}
    	}
    	
    	return $num_repaired;
    }
    
	private function repairNonUniqueNameGlobal($unique_name_field,$model,$concat_field_name,$bRepair)
    {
    	$num_repaired = 0;
    	
    	$data = $model->fetchNonUniqueNameGlobal($unique_name_field);
    	
    	foreach($data as $rec){
    		if ($rec['num'] > 1){
    			$num_repaired++;
    			echo "Unieke naam ".$rec[$unique_name_field]." komt ".$rec['num']." x voor.";
    			
    			if ($bRepair){
    				$records = $model->fetchAllByUniqueName($rec[$unique_name_field],$unique_name_field);
    				foreach($records as $record){
    					
    					$new_name = strtolower($rec[$unique_name_field].$record[$concat_field_name]);    					
    					$model->update($record['id'],array($unique_name_field=>$new_name));    					    					
    				}
    				echo " => Aangepast<br />";
    			}
    			else
    				echo "<br />";
    		}    		
    	}
    	return $num_repaired;
    }
    
	private function repairNonUniqueNameGrouped($unique_name_field,$model,$group_field_name,$group_field_value,$concat_field_name,$bRepair)
    {
    	$num_repaired = 0;
    	
    	$data = $model->fetchNonUniqueNameGrouped($unique_name_field,$group_field_name,$group_field_value);
    	foreach($data as $rec){
    		if ($rec['num'] > 1){
    			$num_repaired++;
    			echo "Unieke naam ".$rec[$unique_name_field]." komt ".$rec['num']." x voor.";
    			
    			if ($bRepair){
    				$records = $model->fetchAllByUniqueName($rec[$unique_name_field],$unique_name_field,$group_field_value,$group_field_name);
    				foreach($records as $record){
    					
    					$new_name = strtolower($rec[$unique_name_field].$record[$concat_field_name]);    					
    					$model->update($record['id'],array($unique_name_field=>$new_name));    					    					
    				}
    				echo " => Aangepast<br />";
    			}
    			else
    				echo "<br />";
    		}    		
    	}
    	return $num_repaired;
    }
    
	private function repairFiles($model,$file_name_field,$mimetype_field,$prefix,$bRepair,$bKeepOriginal=false)
    {
    	$num_repaired = 0;
    	
    	$records = $model->fetchEntries();
    	foreach($records as $record){
    		if (isset($record[$file_name_field]))
    		{
    			if (!file_exists($this->_getUploadDirectory()."/".$record[$file_name_field]))
    			{
    				$num_repaired++;
    				echo "File ".$record[$file_name_field]." bestaat niet.";

    				if ($bRepair)
    				{
    					$model->update($record['id'],array($file_name_field=>NULL,$mimetype_field=>NULL));
    					echo "=> repaired";
    				}
    				
    				echo "<br />";
    			}
    			else
    			{
    				$filename = $prefix.$record['id'].".".$this->_resolveExtention($record[$mimetype_field],$record[$file_name_field]);
    				
    				if ($record[$file_name_field] != $filename)
    				{
    					$num_repaired++;
    					echo "File ".$record[$file_name_field]." heeft verkeerde naam.";
    					if ($bRepair)
    					{
    						if ($bKeepOriginal)
    							copy($this->_getUploadDirectory()."/".$record[$file_name_field],$this->_getUploadDirectory()."/".$filename);
    						else
    							rename($this->_getUploadDirectory()."/".$record[$file_name_field],$this->_getUploadDirectory()."/".$filename);
    							
    						$model->update($record['id'],array($file_name_field=>$filename));
    						echo " => $filename<br />";
    						
    					}
    					else
    						echo "<br />";
    				}
    			}
    		}    		
    	}
    	
    	return $num_repaired;
    }
    
	private function repairUsers($model,$bRepair)
    {
    	$num_repaired = 0;
    	$customerMdl  = new App_Model_Customer();
    	$addrMdl  	  = new App_Model_Address();
    	
    	$records = $model->fetchEntries();
    	foreach($records as $record)
    	{
    		if (($record['user_hash'] === NULL) || ($record['user_hash'] == ''))
    		{	
    			$num_repaired++;
    			echo "User ".$record['user_alias']." heeft geen hash code.";
    			if ($bRepair)
    			{
    				$data['user_hash'] = $this->createhash();
    				$model->update($record['id'],$data);
    				echo "=> repaired<br />";
    			}
    			else
    				echo "<br />";
    		}    		
    	}
    	
    	if ($num_repaired > 0)
    		return $num_repaired;
    		
    	foreach($records as $record)
    	{
    		$data = $customerMdl->fetchByUserId($record['id']);
    		
    		if (!isset($data['id']))
    		{	
    			$num_repaired++;
    			echo "User ".$record['user_alias']." heeft geen klant record.";
    			if ($bRepair)
    			{
    				$customerMdl->save(array('user_id'=>$record['id'],'customer_fullname'=>$record['user_name']));   				
    				
    				echo "=> repaired<br />";
    			}
    			else
    				echo "<br />";
    		}    		
    	}
    	
    	if ($num_repaired > 0)
    		return $num_repaired;
    		
    	foreach($records as $record)
    	{
    		$data = $customerMdl->fetchByUserId($record['id']);
    		
    		if (isset($data['id']))
    		{	
    			$adata = $addrMdl->fetchDefault($data['id']);
    			if (!isset($adata['id']))
    			{
    			
	    			$num_repaired++;
	    			echo "User ".$record['user_alias']." heeft geen adres record.";
	    			if ($bRepair)
	    			{
	    				$ndata = array('address_default'=>1,
	    							   'customer_id'=>$data['id'],
	    							   'address_streetname'=>'onbekend');
	    				
	    				$addrMdl->save($ndata);
	    				echo "=> repaired<br />";
	    			}
	    			else
	    				echo "<br />";
    			}
    		}    		
    	}
    		
    		
    	return $num_repaired;
    }
    
	private function repairUnlinkedFiles($bRepair)
    {
    	$productMdl 		= new App_Model_Product();
    	$articleMdl 		= new App_Model_Article();
    	$categoryMdl 		= new App_Model_Category();
    	$productOptionMdl	= new App_Model_Productoption();
    	$productMdl			= new App_Model_Product();
    	$productpictureMdl	= new App_Model_Productpicture();
    	
    	$num_repaired = 0;
    	
    	$files=array();
		if ($handle = opendir($this->_getUploadDirectory())) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != ".."  && !is_dir($this->_getUploadDirectory().$file)) {
		            $files[] = $file;
		        }
		    }
		    closedir($handle);
		}
		
		foreach($files as $file)
		{
			$fileparts = explode(".",$file);
			$filename  = explode("_",$fileparts[0]);
			$type      = $filename[0];
			
			switch($type."_")
			{
				case Zend_Registry::getInstance()->consts->prefix->articlefiles:
					$data 			= $articleMdl->fetchEntry($filename[1]);
					$target			= $data['article_filename'];
					$targetobject	= "artikel";
					break;
					
				case Zend_Registry::getInstance()->consts->prefix->productfiles:
					
					$data 			= $productMdl->fetchEntry($filename[1]);
					$target			= $data['product_filename'];
					$targetobject	= "product";
					break;					
					
					
				case Zend_Registry::getInstance()->consts->prefix->mailfiles:
					die("$filename : mail: ".$filename[1]);
					break;
				
				case Zend_Registry::getInstance()->consts->prefix->productpictures:
					$data 			= $productpictureMdl->fetchEntry($filename[1]);
					$target			= $data['productpicture_filename'];
					$targetobject	= "product picture";
					break;
				
				case Zend_Registry::getInstance()->consts->prefix->productoptionpicture:
					
					$data 			= $productOptionMdl->fetchEntry($filename[1]);
					$target			= $data['productoption_filename'];
					$targetobject	= "product optie";
					break;
				
				case Zend_Registry::getInstance()->consts->prefix->categorypicture:
					
					$data 			= $categoryMdl->fetchEntry($filename[1]);
					$target			= $data['category_filename'];
					$targetobject	= "categorie";
					break;
					
				default:
					$target 		= "";
					$targetobject 	= "enig object";
					break;
			}	
			
			if ($file != $target)
			{
				$num_repaired++;
	    		echo "File: $file is niet gelinkt aan $targetobject.";
				
	    		if ($bRepair)
	    		{
	    			unlink($this->_getUploadDirectory().$file);
	    			echo " => repaired";	
	    		}
	    		
	    		echo "<br />";
			}
		}
    	
		
    	return $num_repaired;
    }
    
    public function repairAction()
    {
    	$bRepair 		= $this->_getOptionalParam('confirm',false);
    	
    	$article_mdl 		= new App_Model_Article();
    	$category_mdl 		= new App_Model_Category();
    	$catalog_mdl  		= new App_Model_Catalog();
    	$product_mdl  		= new App_Model_Product();
    	$productOption_mdl  = new App_Model_Productoption();
    	$user_mdl			= new App_Model_User();
    	$productpic_mdl     = new App_Model_Productpicture();
    	
    	$num_errors = 0;
    	
    	$this->view->confirmed  = $bRepair;
    	
    		
    	
    	
    	if ($num_errors == 0)
    		$num_errors = $this->repairUsers($user_mdl,$bRepair);
    	
    	$catalogs     	= $catalog_mdl->fetchEntries();
    	if ($num_errors == 0){
	    	foreach($catalogs as $catalog)
	    	{
	    		$categories   = $category_mdl->fetchOrphans($catalog['id']);
	    		foreach($categories as $category)
	    			$num_errors += $this->repairCategorySort($category,$catalog['id'],$bRepair);
	    			
	    		if ($num_errors > 0)
	    			$bRepair = false;
	    	}
    	}
    	
    	$num_errors += $this->repairNonUniqueNameGlobal('category_uniquename',$category_mdl,'id',$bRepair);
    	
    	if ($num_errors == 0)
    		$num_errors += $this->repairFiles($category_mdl,'category_filename','category_mimetype','c_',$bRepair);
    		
    	
    	
    	if ($num_errors == 0)
    	{
    		$categories   = $category_mdl->fetchEntries();
	    	foreach($categories as $category){
	    		$num_errors += $this->repairProductSort($category,$bRepair);
	    		$num_errors += $this->repairNonUniqueNameGrouped('product_uniquename',$product_mdl,
	    												  		 'category_id',$category['id'],
	    												  		 'product_sortkey',$bRepair);	    		 		
	    	}
    	}
    	
    	if ($num_errors == 0)
    		$num_errors = $this->repairFiles($product_mdl,'product_filename','product_filemimetype','p_',$bRepair);
    	
    	if ($num_errors == 0)
    		$num_errors = $this->repairFiles($product_mdl,'product_filename','product_filemimetype','p_',$bRepair);
    		
    	if ($num_errors == 0)
    		$num_errors = $this->repairFiles($productOption_mdl,'productoption_filename','productoption_filemimetype','po_',$bRepair,true);
    		
    	if ($num_errors == 0)
    		$num_errors = $this->repairFiles($article_mdl,'article_filename','article_filemimetype','a_',$bRepair,true);
    	
    	if ($num_errors == 0)
    		$num_errors = $this->repairUnlinkedFiles($bRepair);
    	
    	
    	$this->view->num_errors = $num_errors;
    	
    }
}