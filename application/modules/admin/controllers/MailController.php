<?php

require_once "TableController.php";

class Admin_MailController extends Admin_TableController
{
	const MAIL_SENT  	= 1;
	const MAIL_ERROR	= 2;
	const MAIL_CONFIRM	= 3;
	
	protected function _getTableColumnNames(){
		return array('onderwerp'=>'mail_subject','bijlage'=>'mail_attachment_fileorgname','datum verzonden'=>'mail_datesent');
	}
	protected function _getTableColumnSorts(){
		return array('onderwerp'=>'mail_subject','bijlage'=>'mail_attachment_fileorgname','datum verzonden'=>'mail_datesent');	
	}
	protected function _getJoins(){
		return array( );
	}
	protected function _getAccociatedTables(){
		return NULL;
	}
	protected function _getTableFilterValues(){
		
		return NULL;
	}
	protected function _getRowActions(){
		$actions = parent::_getRowActions();
		
		$actions[] = array('action'=>'send',		'icon'=>'page_go.png',		 'title'=>'verzenden',		'privilege'=>'edit');
		return $actions;
	}
	private function _getSendForm()
	{
		$form 			= $this->_getForm('send');
		$group_element  = $form->getElement('group_id');
		assert($group_element !== NULL);
		
		// get id of group "guests"
		
		$mdl 	= new App_Model_Group();
		$data	= $mdl->fetchEntryByUniqueName(Zend_Registry::getInstance()->roles->roles->guest->name);
		assert(isset($data['id']));		
		$group_element->removeMultiOption($data['id']);
		
		return $form;
	}
	private function _sendMailToUser($email,$alias,$mail_data,$replacement_data,$attachment){
		
		$configArray = array('subject'  => $mail_data['mail_subject'],
							 'to' 		=> $email,
							 'toalias'	=> $alias,
							 'from'		=> Zend_Registry::getInstance()->db_settings->default_mail_fromemail,
							 'fromalias'=> Zend_Registry::getInstance()->db_settings->default_mail_fromalias,
							 'cc'		=> Zend_Registry::getInstance()->db_settings->default_mail_ccemail,
							 'ccalias'	=> "",
							 'bcc'		=> Zend_Registry::getInstance()->db_settings->default_mail_bccemail,
							 'template' => $mail_data['mail_body'],
							 'test'		=> 0);
		
		$replacement_data['url_unsubscribe'] = $_SERVER['HTTP_HOST']."/unsubscribe/".$replacement_data['id']."/".$replacement_data['user_hash'];
		
		$config 	= new Zend_Config($configArray);
		
		$sender     = new App_Mailsender();
		$sender->sendMail($config,$replacement_data,$attachment,NULL);
		
		return true;		
	}
	private function _getChildGroups($group_id){
		
		$gMdl 	= new App_Model_Group();
		$childs = $gMdl->fetchChilds($group_id);
		
		if (empty($childs)){
			return array();
		}
		else
		{
			$childern = array();
			
			foreach($childs as $child)
				$childern[] = $child['id'];

			$childs = $childern;
			foreach($childs as $child){
				
				$cchilds = $this->_getChildGroups($child);
				$childern = array_merge($childern,$cchilds);
			}
			return $childern;
		}
		
	}
	
	private function _getCascadeUsers($group_id){
		
		$gMdl = new App_Model_Group();
		$uMdl = new App_Model_User();
		
		$groups   = $this->_getChildGroups($group_id);
		$groups[] = $group_id;
		
		$all_users = array();
		if (!empty($groups)){			
			foreach($groups as $group_id){
				$users = $uMdl->fetchMailReceivers($group_id);
				$all_users = array_merge($all_users,$users);
			}
		}
		return $all_users;		
	}
	
	private function _sendMailToGroup($group_id,$mail_data,$attachment,$bCascade){
		
		if ($bCascade)
			$all_users = $this->_getCascadeUsers($group_id);
		else
		{
			$uMdl  	   = new App_Model_User();
			$all_users = $uMdl->fetchMailReceivers($group_id);
		}
		
		if (!empty($all_users)){			
			foreach($all_users as $user){
				
				$email = $user['user_email'];
				$alias = $user['user_alias'];
				
				$this->_sendMailToUser($email,$alias,$mail_data,$user,$attachment);	
			}
			return true;
		}
		else
			return false;
	}
	
	protected function _SendMail($id,$group_id,$bCascade=true)
	{
		$mail_data 			= $this->_getModel()->fetchEntry($id);
		$attchment_filename	= $mail_data['mail_attachment'];
		
		if ($attchment_filename !== NULL)
		{
			$attachment['data'] 	= file_get_contents($this->_getUploadDirectory().DIRECTORY_SEPARATOR.$attchment_filename);
			$attachment['filename'] = $mail_data['mail_attachment_fileorgname'];
			$attachment['type'] 	= $mail_data['mail_attachment_filemimetype'];
		}
		else
			$attachment = NULL;
		
		if ($this->_sendMailToGroup($group_id,$mail_data,$attachment,$bCascade) == true)
		{
			$ndata['mail_datesent'] = date("Y-m-d h:m:s");
			
			$this->_getModel()->update($id,$ndata,$attachment);
			
			return self::MAIL_SENT;
		}
		else
			return self::MAIL_ERROR;
	}
	
	protected function _delete($id)
	{		
		// delete filename (indien aanwezig)
		$entry 		= $this->_getModel()->fetchEntry($id);
		$filename 	= $entry['mail_attachment'];
		if ($filename !== NULL){
			
			$filename = $this->_getUploadDirectory().DIRECTORY_SEPARATOR.$filename;
						
			$ret = unlink($filename);
			if ($ret == false)
				$this->LogError("verwijderen mislukt: file $filename bestaat niet.");
		}
		
		return parent::_delete($id);
	}
	
	protected function _handleAddForm($form){
		
		// handle form add
		$mdata 	 = $form->getValues();
		$id 	 = parent::_handleAddForm($form);
		
		// upload bestand (indien aanwezig)
		$this->_handleUpload($form,$id,
							 Zend_Registry::getInstance()->consts->prefix->mailfiles,
							 $mdata['mail_attachment'],'mail_attachment','mail_attachment_fileorgname','mail_attachment_filemimetype');
		
		return $id;		
    }
    
	protected function _getFilenameFieldname(){
		return "mail_attachment";
	}
	
	protected function _getFilemimetypeFieldname(){
		return "mail_attachment_fileorgname";
	}
	
	protected function _getFileorgnameFieldname(){
		return "mail_attachment_filemimetype";
	}
	
	protected function _getFilenamePrefix(){
		return Zend_Registry::getInstance()->consts->prefix->mailfiles;
	}
    
    /*
	protected function _handleEditForm($form){		
		$request 	     = $this->getRequest();
		$id      	  	 = $request->getParam('id',NULL);
		
		$data			 = $this->_getModel()->fetchEntry($id);
		assert(isset($data));
		
		return $this->_handleUpload($form,
									$id,
									Zend_Registry::getInstance()->consts->prefix->mailfiles,
									$data['mail_attachment'],
									'mail_attachment',
									'mail_attachment_fileorgname',
									'mail_attachment_filemimetype');	
    }
    */
    
    
	
	protected function _handleUnconfirmedSendForm($form)
	{
		$cascade			= $this->_getOptionalParam('cascade',true);
		
		$data			 	= $form->getValues();
		$id				 	= $this->_getRequiredParam('id');
		$mail_data       	= $this->_getModel()->fetchEntry($id);
		
		if ($cascade)
			$this->view->users 	= $this->_getCascadeUsers($data['group_id']);
		else
		{
			$uMdl  	   			= new App_Model_User();
			$this->view->users	= $uMdl->fetchMailReceivers($data['group_id']);
		}
		
		$this->view->group  = $data['group_id'];
		
		return self::MAIL_CONFIRM;
	}
	
	public function sendAction(){
		
		$request 					= $this->getRequest();
		$mail_id					= $this->_getRequiredParam('id');
		$confirmed					= $this->_getOptionalParam('confirmed',0);
		$group_id					= $this->_getOptionalParam('group_id',0);
		$cascade					= $this->_getOptionalParam('cascade',1);
		$this->view->show_confirm   = false;
		$this->view->id			    = $mail_id;
		
		if ($confirmed == 0)
		{
			
			$ret = $this->_handleForm($this->_getSendForm(),NULL,"_handleUnconfirmedSendForm");
			
			if ($ret == self::MAIL_CONFIRM)
				$this->view->show_confirm  = true;
				
			$this->view->cascade = $cascade;
		}
		else
		{
			$ret = $this->_SendMail($mail_id,$group_id,$cascade);
			
			return $this->_helper->redirector('index',$this->_controller);
		}
	}
	
}