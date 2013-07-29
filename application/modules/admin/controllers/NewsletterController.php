<?php

require_once "TableController.php";

class Admin_NewsletterController extends Admin_TableController
{
	private $_sender = NULL;
	
	private function _getSender(){
		if ($this->_sender === NULL)
			$this->_sender = new App_NewsletterSender_YMLP(Zend_Registry::getInstance()->site->newsletter->provider->apikey,
    													   Zend_Registry::getInstance()->site->newsletter->provider->username);
		
		return $this->_sender;
	}
	
	protected function _getTableColumnNames(){
		return array('naam'=>'newsletter_name','onderwerp'=>'newsletter_subject','status'=>'newsletter_status');	
	}
	
	protected function _getTableColumnSorts(){
		return array('naam'=>'newsletter_name','onderwerp'=>'newsletter_subject','status'=>'newsletter_status');	
	}
	
	protected function _getTableColumnDecoder(){
		return array('newsletter_status'=>array(0=>'Nieuw',1=>'Verstuurd'));
	}
	
	protected function _getJoins(){
		return array();
	}
	
	protected function _getRowActions(){
		$actions = parent::_getRowActions();		
		$actions[] = array('action'=>'copy','icon'=>'copy.png','title'=>'kopieeren','privilege'=>'add');
		$actions[] = array('action'=>'view','icon'=>'view.png','title'=>'bekijken','privilege'=>'index','target'=>'_blank');
		$actions[] = array('action'=>'test','icon'=>'bug.png','title'=>'test','privilege'=>'send');
		$actions[] = array('action'=>'send','icon'=>'page_go.png','title'=>'versturen','privilege'=>'send',
						   'enabled_condition'=>array('field'=>'newsletter_status','value'=>0));
		
		return $actions;
	}
	
		
	protected function _getAccociatedTables(){
		return array();
	}
	
	protected function _getTableFilterValues(){
		return NULL;
	}
	
	protected function checkDeleteAllowed($id)
	{
		return "niet mogelijk";
	}
	
	protected function _update($id,$data)
	{
		unset($data['newsletter_uniquename']);
		unset($data['newsletter_status']);
		
		return parent::_update($id,$data);
	}
	
	protected function _getCopyForm(){
		return $this->_getForm("copy");
	}
	
	protected function _handleCopyForm($form){
		
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);
		
		$data    	= $this->_getModel()->fetchEntry($id);
		$new_data	= $form->getValues();
		
		$data['newsletter_uniquename'] = $new_data['newsletter_uniquename'];
		$data['newsletter_status']     = 0;
		unset($data['id']);
				
    	return $this->_save($data);   	
    }
	
	public function copyAction(){
		
		$request 			 = $this->getRequest();
				
		$ret = $this->_handleForm($this->_getCopyForm(),NULL,"_handleCopyForm");
		
		if (($ret != self::FORM_NOT_POSTED) && ($ret != self::FORM_INVALID))
		{
			if ($ret === NULL)
				throw new Exception("TableController::copyAction(): geen waarde geretouneerd door save actie.");
			
			if ($this->_isAllowed('edit'))
    			return $this->_redirectAfterAdd($ret);
    		else
    			return $this->_helper->redirector('index',$this->_controller);
		}
		
		$this->view->actions			= $this->_getTableActions();
		$this->view->domain_displayname	= $this->_getDisplayName();
				
		return $this->render('table/copy',null,true);
	}
	
	public function testAction(){
		
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);

		$data    = $this->_getModel()->fetchEntry($id);
		
		
		assert(isset($data['id']));
		
		$this->_getSender()->test($data['newsletter_subject'],$data['newsletter_html'],$data['newsletter_text'],"");
		
	}
	
	public function viewAction(){
		
		$request = $this->getRequest();
		$id      = $request->getParam('id',NULL);	
		$data    = $this->_getModel()->fetchEntry($id);
		
		$this->_redirector = $this->_helper->getHelper('Redirector');
   		return $this->_redirector->gotoRoute(array('name'=>$data['newsletter_uniquename']),'newsletter_view');
	}
	
	public function sendAction(){
		
		$request 	= $this->getRequest();
		$id      	= $request->getParam('id',NULL);
		$confirmed 	= $request->getParam('confirm',false);
		$data    	= $this->_getModel()->fetchEntry($id);
		$subsMdl    = new App_Model_Subscriber();
		
		if ($confirmed){
			
			$ret = $this->_getSender()->send($data['newsletter_subject'],$data['newsletter_html'],$data['newsletter_text'],"");
			if ($ret){
				$data['newsletter_status'] = 1;
				$this->_getModel()->update($id,$data);
			}
			else
				die("Error: ".$this->_getSender()->lastResult());
			
			$this->result = $this->_getSender()->lastResult();
			
			$this->render('newsletter/sent',null,true);
		}
		else
		{
			$num_subscribers = $subsMdl->fetchNumSubscribers();
			
			if ($num_subscribers > 0){
				$this->view->name				= $data['newsletter_name'];
				$this->view->num_subscribers	= $num_subscribers;
				$this->view->id 				= $id;
				$this->view->ctrl 				= strtolower($this->_domain_name);			
				$this->render('newsletter/confirm',null,true);
			}
			else
				$this->render('newsletter/nosubscribers',null,true);
		}		
	}	
}