<?php

class App_View_Helper_TableCSV extends Zend_View_Helper_Abstract
{
	public function TableCSV($helper,$fieldsEnclosedBy='"',$fieldsSeparatedBy=';',$linesSeparatedBy=PHP_EOL,$includeHeaders=true)
   	{
		$cols 		= $helper->getBodyCols();
		$entries	= $helper->getAllEntries();
		$output     = "";
		
		if (($includeHeaders) && (count($entries) > 0)){
			$hdr        = $helper->getHeaderRows();
			
			foreach($hdr[0] as $hdrname){
				
				//Zend_Debug::dump($hdrname);die();
				
				$output .= $fieldsEnclosedBy.$hdrname['label'].$fieldsEnclosedBy.$fieldsSeparatedBy;
			}
			
			$output .= $linesSeparatedBy;
		}
		
		foreach($entries as $entry){			
			foreach($cols as $col){
				
				$output .= $fieldsEnclosedBy;	

				if ($col['fieldname'] !== NULL)
					$value   = $entry[$col['fieldname']];
				else
					$value   = $entry;
					
				if ($col['formatfunc'] != ""){
					$value = $helper->callFormatFunction($col['formatfunc'],$value);
				}
					
				if ($col['viewhelper'] != "")
					$value = $helper->callViewHelper($col['viewhelper'],$value);
				
				$output .= $col['prefix'].trim($value);
				$output .= $fieldsEnclosedBy.$fieldsSeparatedBy;

				
			}
			$output .= $linesSeparatedBy;
			
			//Zend_Debug::dump($output);die();
		}
		
		return trim($output);

   	}
}