<?php 

class Admin_View_Helper_GetFileIcon
{
   public function GetFileIcon($type)
   {
   		switch($type){
   			case Zend_Registry::getInstance()->types->images->jpeg: 		return "image_icon.png";
   			case Zend_Registry::getInstance()->types->images->gif: 			return "image_icon.png";   			
   			case Zend_Registry::getInstance()->types->nonimages->pdf: 		return "pdf_icon.png";
   			case Zend_Registry::getInstance()->types->nonimages->word: 		return "word_icon.png";
   			case Zend_Registry::getInstance()->types->nonimages->excel: 	return "excel_icon.png";
   			default: return "unknown_icon.png";
   		}
   	
   	
   		
   }
}