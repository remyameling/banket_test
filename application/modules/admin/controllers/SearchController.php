<?php

require_once "BaseController.php";

class Admin_SearchController extends Admin_BaseController
{
	
	private $msg = "";
	
	private function sanitize($input) {
		return htmlentities(strip_tags( $input ));
	}
	
	private function addArticle($index,$id){
    	
    	$model = new App_Model_Article();
    	$data  = $model->fetchEntry($id);
    	
    	if (isset($data['id'])){
    		
    		// check if article is published
    		
    		if ($model->isPublished($data))
        	{
        		$w_mdl  = new App_Model_Weblog();
	    		$wdata  = $w_mdl->fetchEntry($data['weblog_id']);
	    		
	    		$doc = new Zend_Search_Lucene_Document();
	    		
	    		$options = array('module'=>'default','controller'=>'article','action'=>'view',
	    						 'article'=>$data['article_uniquename'],'weblog'=>$wdata['weblog_uniquename']);
	    		
	    		$helper = $this->_helper->url;
	    		
	    		
	    		$url = $helper->url($options,'wla_'.$wdata['weblog_uniquename'],true);
	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',$this->sanitize($url)));
	    		$doc->addField(Zend_Search_Lucene_Field::Keyword('pagetype',Zend_Registry::getInstance()->consts->search->pagetype->article));	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Keyword('uid',Zend_Registry::getInstance()->consts->search->uid->prefix->article.$data['id']));
	    		
	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Text('title',$this->sanitize($data['article_title'])));	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Text('objectid',$data['id']));
	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Unstored('summary',$this->sanitize($data['article_summary'])));
	    		$doc->addField(Zend_Search_Lucene_Field::Unstored('content',$this->sanitize($data['article_content'])));
	    		
	    		$index->addDocument($doc);
        	}
    	}
    }
    
	private function addProduct($index,$id){
    	
    	$model = new App_Model_Product();
    	$data  = $model->fetchEntry($id);
    	
    	if (isset($data['id'])){
    		
    		// check if product is published
    		
    		if ($model->isPublished($data))
        	{
        		$c_mdl  = new App_Model_Category();
	    		$cdata  = $c_mdl->fetchEntry($data['category_id']);
	    		
	    		$doc = new Zend_Search_Lucene_Document();
	    		
	    		$options = array('module'=>'catalog','controller'=>'product','action'=>'view',
	    						 'product'=>$data['product_uniquename'],'category'=>$cdata['category_uniquename']);
	    		
	    		$helper = $this->_helper->url;    		
	    		$url = $helper->url($options,'product',true);
	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',$this->sanitize($url)));
	    		$doc->addField(Zend_Search_Lucene_Field::Keyword('pagetype',Zend_Registry::getInstance()->consts->search->pagetype->product));
	    		$doc->addField(Zend_Search_Lucene_Field::Keyword('uid',Zend_Registry::getInstance()->consts->search->uid->prefix->product.$data['id']));
	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Text('title',$this->sanitize($data['product_name'])));
	    		$doc->addField(Zend_Search_Lucene_Field::Text('objectid',$data['id']));
	    		
	    		//echo "add product met objectid:".$data['id']."<br />";
	    		
	    		$doc->addField(Zend_Search_Lucene_Field::Unstored('content',$this->sanitize($data['product_content'])));
	    			    		
	    		$index->addDocument($doc);
        	}
    	}
    	
    }
    
    public function createAction()
    {
    	$directory = $this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_search_lucene;
    	$index 	   = Zend_Search_Lucene::create($directory);
    	
    	$index->setMergeFactor(1);
    	$index->setMaxMergeDocs(2147483647);	
    	
    	$this->view->msg = "...zoek index aangemaakt";  	
    	
    	$this->render('message');
    	
    }
    
    public function articlesAction()
    {
    	$weblog_id 		= $this->_getRequiredParam('weblog');
    	$weblog_name 	= $this->_getRequiredParam('name');
    	
    	$model 		= new App_Model_Article();
    	$articles	= $model->fetchByWeblog($weblog_id);
    	
    	if (count($articles) > 0)
    	{
	    	$directory  = $this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_search_lucene;
	    	$index 	    = Zend_Search_Lucene::open($directory);
	    	
	    	foreach($articles as $article)
	    		$this->addArticle($index,$article['id']);
	    		
	    	$index->optimize();
    		$index->commit();
    		
    		$this->view->msg = "...weblog $weblog_name bijgewerkt (".count($articles)." artikels)";    	
    		$this->render('message');    	
    	}
    	else
    		$this->_helper->viewRenderer->setNoRender(true);
    	
    	
    }
    
    public function productsAction()
    {
    	$category_name 	= $this->_getRequiredParam('category');
    	
    	$model 		= new App_Model_Product();
    	$products	= $model->fetchByCategoryName($category_name); 

    	if (count($products) > 0)
    	{
    		$directory  	= $this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_search_lucene;
    		$index 	    	= Zend_Search_Lucene::open($directory);
    		
    		foreach($products as $product)    		
    			$this->addProduct($index,$product['id']);    		
    	
    		$index->optimize();
    		$index->commit();
    	
    		
    		$this->view->msg = "...categorie $category_name bijgewerkt (".count($products)." produkten)";  
    	
    		$this->render('message');
    	}
    	else
    		$this->_helper->viewRenderer->setNoRender(true);
    	
    	
    }
    
    public function indexAction()
    {
    	$catMdl     = new App_Model_Category();
    	$categories = $catMdl->fetchEntries();
    	
    	$wlMdl     = new App_Model_Weblog();
    	$weblogs   = $wlMdl->fetchEntries();
    	
    	//set_time_limit(60);
    	
    	if (count($categories) > 0)
    		foreach($categories as $category)
    			$this->_helper->actionStack('products','search','admin',array('category'=>$category['category_uniquename']));
    			
    	if (count($weblogs) > 0)
    		foreach($weblogs as $weblog)
    			$this->_helper->actionStack('articles','search','admin',array('weblog'=>$weblog['id'],'name'=>$weblog['weblog_name']));
    			
    	
    	$this->_helper->actionStack('create','search','admin');
    	
    	
    	
    	$this->_helper->layout()->enableLayout();
    }
    
    public function checkAction()
    {
    	$repair     	= $this->_getOptionalParam('repair',false);
    	$max_repairs	= 10;
    	$num_repairs    = 0;
    	
    	if ($repair != false)
    		$repair = true;
    		
    	
    	
    	$directory  = $this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_search_lucene;
    	$index 	    = Zend_Search_Lucene::open($directory);
    	
    	
    	
    	$pMdl 		= new App_Model_Product();
    	$products	= $pMdl->fetchEntries();  
    	
    	foreach($products as $product)
    	{
    		if ($pMdl->isPublished($product))
        	{
	    		$term 	= new Zend_Search_Lucene_Index_Term(Zend_Registry::getInstance()->consts->search->uid->prefix->product.$product['id'], 'uid');
	    		$docIds	= $index->termDocs($term);
	    		
	    		if (!isset($docIds[0]))
	    		{
	    			if (($repair) && ($num_repairs < $max_repairs))
	    			{
	    				$this->addProduct($index,$product['id']);
	    				$index->commit();
	    				$index->optimize();
	    				
	    				$num_repairs++;
	    				echo "product met id ".$product['id']." (".$product['product_uniquename'].") toegevoegd<br />";
	    			}
	    			else 
	    				echo "product met id ".$product['id']." (".$product['product_uniquename'].") niet geindexeerd<br />";
	    		}
        	}
    		
    	}
    	
    	$aMdl 		= new App_Model_Article();
    	$articles	= $aMdl->fetchEntries();  
    	
    	foreach($articles as $article)
    	{
    		if ($aMdl->isPublished($article))
    		{
	    		$term 	= new Zend_Search_Lucene_Index_Term(Zend_Registry::getInstance()->consts->search->uid->prefix->article.$article['id'], 'uid');
	    		$docIds	= $index->termDocs($term);
	    		
	    		if (!isset($docIds[0]))
	    		{
	    			if (($repair) && ($num_repairs < $max_repairs))
	    			{
	    				$this->addArticle($index,$article['id']);
	    				$index->commit();
	    				$index->optimize();
	    				
	    				$num_repairs++;
	    				echo "artikel met id ".$article['id']." (".$article['article_uniquename'].") toegevoegd<br />";
	    			}
	    			else	    			
	    			{
	    				echo "artikel met id ".$article['id']." (".$article['article_uniquename'].") niet geindexeerd<br />";
	    			}
	    		}
    		}
    		
    	}
    	
    	
    	
    	$this->render('message'); 
    }
    
    
    
    
}