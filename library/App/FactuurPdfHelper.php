<?php

class App_FactuurPdfHelper
{
	const MAX_ITEMS_FIRST_PAGE	= 38;
	const MAX_ITEMS_OTHER_PAGE  = 28;
	
	private function _groupItemsPerPage($items){
		
		/* opsplitsen items per pagina */
		
		$page_num	    	  = 0;
		
		if (count($items) > self::MAX_ITEMS_FIRST_PAGE)
		{
			$page_items[$page_num] 	= array_slice($items,0,self::MAX_ITEMS_FIRST_PAGE);
			$items 					= array_slice($items,self::MAX_ITEMS_FIRST_PAGE);
			
			while(count($items) > self::MAX_ITEMS_OTHER_PAGE)
			{
				$page_num++;
				$page_items[$page_num] = array_slice($items,0,self::MAX_ITEMS_OTHER_PAGE);
				$items 				   = array_slice($items,self::MAX_ITEMS_OTHER_PAGE);				
			}
			
			$page_items[$page_num+1] = $items;
		}
		else
			$page_items[0] = $items;
		
		return $page_items;
		
	}
	
	
	
	public function render($factuur_data,$items)
	{
		$render_data 	  = $factuur_data;
		
		
		$template 		  = APPLICATION_PATH."/../sites/".WEBSITE.
					  		Zend_Registry::getInstance()->paths->pdf_templates."/".
					  		Zend_Registry::getInstance()->site->factuur_template_name;
					  
		$config		     = APPLICATION_PATH."/../sites/".WEBSITE.
					  		Zend_Registry::getInstance()->paths->pdf_templates."/".
					  		Zend_Registry::getInstance()->site->factuur_config;
					  		
		$items_per_page   = $this->_groupItemsPerPage($items);
		$num_pages		  = count($items_per_page);
		$last_page		  = $num_pages;
	
		/* pagina('s) renderen */
					  
		$render_data['items'] 	= $items_per_page[0];
		$render_data['curpage'] = 1;
		$render_data['maxpage'] = "/ ".$num_pages;
					  
		if ((file_exists($template)) && (file_exists($config)))
		{
			// first page
			if ($num_pages == 1)
				$section = 'firstandlast';
			else
				$section = 'first';
				
			$pdfHelper 	 = new App_PdfCreator($template);
			$pdfHelper->addPage($config,$render_data,$section);
			
			$page = 2;
			while ($page <= $num_pages)
			{
				if ($page == $num_pages)
					$section = 'last';
				else
					$section = 'all_pages';
				
				$render_data['items'] 	= $items_per_page[$page-1];
				$render_data['curpage'] = $page;
				$pdfHelper->addPage($config,$render_data,$section);
				$page++;	
			}
			
			return $pdfHelper->renderPdf(false);		
		}
		else
		{
			die("FactuurPdfHelper::render(): config file of template niet gevonden.");
			return NULL;
		}	
	}
}