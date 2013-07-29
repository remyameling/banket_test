<?php

require_once "TableController.php";

class Admin_SubscriberController extends Admin_TableController
{
	protected function _getTableColumnNames(){
		return array('email'=>'subscriber_email',
					 'geslacht'=>'subscriber_sex',
					 'voornaam'=>'subscriber_firstname',
					 'achternaam'=>'subscriber_lastname');	
	}
	
	protected function _getTableColumnSorts(){
		return array('email'=>'subscriber_email',
					 'geslacht'=>'subscriber_sex',
					 'voornaam'=>'subscriber_firstname',
					 'achternaam'=>'subscriber_lastname');	
	}
	
	protected function _getTableColumnDecoder(){
		return array('subscriber_sex'=>array(0=>'dhr',1=>'mevr',2=>''));
	}
	
	protected function _getJoins(){
		return array();
	}
	
	protected function _getAccociatedTables(){
		return array();
	}
	
	protected function _getTableFilterValues(){
		return NULL;
	}
	
	protected function checkDeleteAllowed($id)
	{
		return NULL;
	}
	
		
}