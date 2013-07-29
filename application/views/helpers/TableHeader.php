<?php

class App_View_Helper_TableHeader extends Zend_View_Helper_Abstract
{
	public function TableHeader($helper,$bIncludeHeaderTags=true)
   	{
		$rows = $helper->getHeaderRows();
		
		$output = "";
		if ($bIncludeHeaderTags)
			$output .= "<thead>".PHP_EOL;
		
		
		foreach($rows as $row){
			$output .= "<tr>".PHP_EOL;
			foreach($row as $col){
				
				$class 			= '';
				$colspan 		= '';
				$hrefprefix 	= '';
				$hrefpostfix 	= '';
				
				if ($col['class'] != "")
					$class = 'class="'.$col['class'].'"';
				
				if ($col['colspan'] != 1)
					$colspan = 'colspan="'.$col['colspan'].'"';
					
				$output .= "<th $class $colspan>".PHP_EOL;
				
				if ($col['sortfield'] !== NULL)
				{
					
					$url            = $this->view->url(array('order'=>$col['sortfield'],'sort'=>$helper->getInvSort(),'page'=>1));
					
					$hrefprefix 	= '<a href="'.$url.'">';
					$hrefpostfix 	= '</a>';
				}
				
				$output .= $hrefprefix.$col['label'].$hrefpostfix;
				$output .= "</th>".PHP_EOL;
			}
			$output .= "</tr>".PHP_EOL;
		}	
		
		if ($bIncludeHeaderTags)
			$output .= "</thead>".PHP_EOL;
		
		return $output;

   	}
}