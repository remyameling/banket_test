<?php 

class Admin_View_Helper_NavIndexes
{
   protected $_logGroup 	= "ACL";	
	
   public function NavIndexes()
   {
   		$nav_items  = Zend_Registry::getInstance()->admin_navigation->nav->toArray();
   		
   		$urls         = array();
   		$role         = App_Auth_Auth::getInstance()->getRole();
   		
   		foreach($nav_items as $controller_name){   			
   			
   			if ($controller_name !== 'index'){   				   				
   				$resource  = "admin_".$controller_name;
   				
   				if (Zend_Registry::getInstance()->acl->isAllowed($role,$resource,'index')){   					
   					$config = Zend_Registry::getInstance()->admin_const->controller->get($controller_name);
   					
   					if ($config === NULL)
   						throw new Exception("Geen entry gevonden in admin/const.ini voor controller $controller_name");
   					$domainDisplayName = $config->domaindisplayname;
   					
   					$urls[] = array('module'=>'admin','controller'=>$controller_name,'action'=>'index','name'=>$domainDisplayName);
   					
   				}
   			}
   		}
		
		return $urls;
   }
}