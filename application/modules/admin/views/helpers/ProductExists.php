<?php 

class Admin_View_Helper_ProductExists
{
   private $_model = NULL; 
	
	
   private function _getModel(){
   		if ($this->_model === NULL){
   			$this->_model = new App_Model_Product();
   		}
   		return $this->_model;
   }
   
   public function ProductExists($id)
   {
   		if ($id !== NULL)
   		{
   			$data = $this->_getModel()->fetchEntry($id);
   			if (isset($data['id']))
   				return true;
   		}
   		
   		return false;
   }
}