<?php

class App_View_Helper_TablePagination extends Zend_View_Helper_Abstract
{
	public function TablePagination($helper,$partialviewscript,$type="Siding")
   	{
		return $this->view->paginationControl($helper->getPagedEntries(),$type,$partialviewscript); 
   	}
}