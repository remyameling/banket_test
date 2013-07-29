<?php

require_once "GroupedTableController.php";

class Admin_ProductController extends Admin_GroupedTableController
{
	protected function _getTableColumnNames(){
		return array('naam'=>'product_naam','source'=>'product_external_src','prijs'=>'product_nettoprijs');	
	}
	
	protected function _getTableColumnSorts(){
		return $this->_getTableColumnNames();
	}
	
	protected function _getTableColumnDecoder(){
		return array();
	}

	protected function _getJoins(){
		return NULL;
	}
	
	protected function _getAccociatedTables(){
		
		return array(/*array('title'=>'Toon alle leveranciers voor deze produkten',
						   'controller'=>'user',
						   'action'=>'index',
						   'params'=>array('ff'=>'group_id'),
						   'id_param'=>'fv',
						   'id_field'=>'id',						   		
						   'icon'=>'user_small.png')*/);
	}
	
	
	
	protected function _getGroupModel(){
		if ($this->_groupedModel == NULL)
			$this->_groupedModel = new App_Model_Category();
			
		return $this->_groupedModel;
	}
	
	protected function _getGroupControllerName(){
		return "category";
	}	
	
	protected function _getGroupControllerDisplayName(){
		return "categorie";
	}
	
	protected function _getGroupTableLabelField(){
		return "category_name";
	}
	
	protected function _getGroupByField(){
		return "category_id";
	}
	
	
	
	protected function _getLeveranciersData($id){
				
		// ophalen alle mogelijke leveranciers
		$lMdl  			= new App_Model_Leverancierproduct();
		$leveranciers 	= $lMdl->fetchByProduct($id);
		
		return $leveranciers;
	}
	
	protected function editRender()
    {
    	return $this->render('product/edit',NULL,true);
    }
    
	public function editAction()
	{
		$id  = $this->_getRequiredParam('id');
		$tab = $this->_getOptionalParam('t','default');
		
		if ($tab == 'default')
			$tabidx = 0;
		else
			$tabidx = 1;
		
		$this->view->leveranciers = $this->_getLeveranciersData($id);	
		$this->view->id			  = $id;
		$this->view->tabidx		  = $tabidx;
		
		parent::editAction();	
	
	}
	
	protected function _handleImportForm($form)
	{
		$source  	= $this->_getRequiredParam('source');
		$mdl	    = new App_Model_Product();
		$catMdl		= new App_Model_Category();
		
		
				
		$config  	= Zend_Registry::getInstance()->site->productsource->get($source)->db;
		if ($config === NULL)
			throw new Exception("confugiratie voor product source $source niet gevonden in .ini file (site.ini)");
		
		$dbAdapter 	= Zend_Db::factory($config);
		
		// get products from source
		$products   = $mdl->importFromSource($dbAdapter,$source);
		
		// verwijder alle eerder geimporteerde produkten van deze source
		$mdl->deleteImported($source);
		
		// voeg geimporteerde produkten toe
		foreach($products as $product){
			
			// check if category met unieke naam bestaat
			
			if ($catMdl->exists($product['category_uniquename']))
			{
				$catData 		= $catMdl->fetchByName($product['category_uniquename']);
				$category_id 	= $catData['id'];
			}
			else
			{
				$category_id    = $catMdl->save(array('category_uniquename'=>$product['category_uniquename'],
													  'category_name'=>$product['category_name'],
													  'catalog_id'=>0));
			}
			
			$data['category_id'] 		  = $category_id;
			$data['product_external_src'] = $source;
			$data['product_external_uid'] = $product['id'];
			$data['product_naam'] 		  = $product['product_naam'];
			$data['product_omschrijving'] = $product['product_omschrijving'];
			$data['product_nummer'] 	  = $product['product_nummer'];
			$data['product_nettoprijs']   = $product['product_price'];
			
			
			$mdl->save($data);
			
		}
		
		// verwijder alle categorieen die nu leeg zijn
	}
	
	public function importAction()
	{
		$ret = $this->_handleForm($this->_getForm('import'),NULL,"_handleImportForm",NULL);
		
		if (($ret != self::FORM_NOT_POSTED) && ($ret != self::FORM_INVALID) && ($ret != self::FORM_ACTIONSTACK))
		{
			return $this->_helper->_redirector('index','product','admin');
	    }
	}
	
	public function _getTableActions(){
		
		$actions 	= parent::_getTableActions();
		
		if ($this->_isAllowed('add')){
			$actions[] 	= $this->_getActionUrl('import','import');
		}
    	
    	
    	
    	return $actions;
    }
}