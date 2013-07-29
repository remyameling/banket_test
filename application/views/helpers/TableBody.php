<?php

class App_View_Helper_TableBody extends Zend_View_Helper_Abstract
{
	public function TableBody($helper,$bIncludeBodyTags=true)
   	{
		$cols 		= $helper->getBodyCols();
		$entries	= $helper->getPagedEntries();
		
		$output = "";
		if ($bIncludeBodyTags)
			$output .= "<tbody>".PHP_EOL;
		
		
   		foreach($entries as $entry){
			$output .= "<tr>".PHP_EOL;
			foreach($cols as $col){
				
				$class 			= '';
				$colspan 		= '';
				
				
				if ($col['class'] != "")
					$class = 'class="'.$col['class'].'"';
				
				if ($col['colspan'] != 1)
					$colspan = 'colspan="'.$col['colspan'].'"';
					
				$output .= "<td $class $colspan>".PHP_EOL;	

				if ($col['fieldname'] !== NULL)
					$value   = $entry[$col['fieldname']];
				else
					$value   = $entry;
				
				if ($col['formatfunc'] != ""){
					$value = $helper->callFormatFunction($col['formatfunc'],$value);
				}
					
				if ($col['viewhelper'] != "")
					$value = $helper->callViewHelper($col['viewhelper'],$value);
				
				$output .= $col['prefix'].$value;
				$output .= "</td>".PHP_EOL;
			}
			$output .= "</tr>".PHP_EOL;
		}
		
		if ($bIncludeBodyTags)
			$output .= "</body>".PHP_EOL;
		
		return $output;

   	}
}