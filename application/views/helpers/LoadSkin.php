<?php

class App_View_Helper_LoadSkin extends Zend_View_Helper_Abstract
{
	public function loadSkin($skin=NULL)
	{	
		// load the skin config file
		if ($skin === NULL){
			$skin = Zend_Registry::getInstance()->db_settings->skin;
		}
		
		$filename = Zend_Registry::getInstance()->paths->url_dir_skin."/".$skin.'/skin.xml';
		
		if (!file_exists($filename))
			die("Skin file ($filename) niet gevonden.");
		
		$skinData 	 = new Zend_Config_Xml($filename);		
		$stylesheets = $skinData->stylesheets->stylesheet->toArray();
		
		//print_r($stylesheets);
		//die();
		
		// append each stylesheet
		
		if(is_array($stylesheets))
		{
			foreach($stylesheets as $stylesheet){
			
				if (!isset($stylesheet['media']))
					$media = "screen";
				else
					$media = $stylesheet['media'];
					
				$href = '/skins/'.$skin.'/css/'.$stylesheet['filename'];
			
				$this->view->headLink()->appendStylesheet($href,$media);
			}				
			
		}	
		else
			die("NOK");	
	}
}