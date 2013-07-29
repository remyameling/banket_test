<?php 

class Default_View_Helper_Mainnavigation extends Zend_View_Helper_Abstract
{
   protected $_logGroup 	= "ACL";	
   
   public function RenderMenuItem($item){
   	
   		$url   = $this->view->url(array('module'=>$item->module,'controller'=>$item->controller,'action'=>$item->action),null,true);
   		
   		if (isset($item->class))
   			$class = 'class="'.$item->class.'"';
   		else
   			$class = '';
   			
   		return '<li '.$class.'><a href="'.$url.'">'.$item->label.'</a></li>';
   }
   
   public function RenderMenu($role,$ulclass,$items){
   	
   		$html 		= '<ul class="'.$ulclass.'">';
   		$hasitems 	= false;
   	
   		foreach($items as $item){
   			if ((isset($item->module)) && (isset($item->controller))){
   				$resource  = $item->module."_".$item->controller;
   				if (Zend_Registry::getInstance()->acl->isAllowed($role,$resource,$item->action)){
   					$html .= $this->RenderMenuItem($item);
   					$hasitems = true;
   				}
   			}
   			else{
   				$label = '<li><a>'.$item->label.'</a>';
   				if (isset($item->sub)){
   					
   					$submenu = $this->RenderMenu($role,"",$item->sub);
   					if ($submenu != ""){
   						$html .= $label.$submenu.'</li>';
   						$hasitems = true;
   					}
   				}   				
   			}
   		}
   		
   		$html .= "</ul>";
   		
   		if ($hasitems)
   			return $html;
   		else
   			return "";
   	
   }
	
   public function Mainnavigation($ulclass,$nav_items)
   {
   		
   		//print_r($nav_items->toArray());
   		//die();
   		
   		
   		$role  = App_Auth_Auth::getInstance()->getRole();
   		$html  = $this->RenderMenu($role,$ulclass,$nav_items); 
   		
   		return $html;
   }
}