<?php 

require_once APPLICATION_PATH.Zend_Registry::getInstance()->paths->components."BaseController.php";

abstract class Admin_BaseController extends RACCMS_Component_BaseController //extends Zend_Controller_Action
{
	const FORM_NOT_POSTED   = -2;
	const FORM_INVALID  	= -1;
	const FORM_HANDLED  	= 0;
	protected $_logGroup 	= "ADMIN";
	
	protected $_form = NULL;
	
	function _getFormConfig(){
		return Zend_Registry::getInstance()->admin_forms;
	}
	
	public function init()
	{
		$this->initLayout(Zend_Registry::getInstance()->paths->layout->admin->default,
						  APPLICATION_PATH.'/layouts/admin/scripts/');				
		
		$this->view->addHelperPath(APPLICATION_PATH.'/views/helpers','App_View_Helper_');
		
		return parent::init();
		
    }
    
	protected function _getForm($form="add")
    {
		$config = Zend_Registry::getInstance()->admin_forms->get(strtolower($this->_domain_name));
			
		if ($config === NULL)
			throw new Exception("Admin_BaseController::_getForm(".$form."): geen formulier entry gevonden voor ".$this->_domain_name." in admin forms.ini");
			
		assert($config !== NULL);
			
		$this->_form = new RAC_Component_Form($config,$form);
		return $this->_form;
	}
	
	
    
	protected function _handleForm($form,$default_data,$process_func,$action=NULL){
	
		assert($form !== NULL);
		assert($process_func !== NULL);
	
		$request = $this->getRequest();
		
		if ($default_data !== NULL)
			$form->setDefaults($default_data);
			
		if ($action !== NULL)
			$form->setAction($action);
			
		// assign the form to the view
		$this->view->form = $form;	
		
		// check to see if this action has been POST'ed to
		if ($this->getRequest()->isPost()) {
			
			// now check to see if the form submitted exists, and
			// if the values passed in are valid for this form
			if ($form->isValid($request->getPost())) {
				
				return $this->$process_func($form);	
			}
			else
				return self::FORM_INVALID;
		}
		else
			return self::FORM_NOT_POSTED;			
	}
	
	
	protected function _getMimeTypeFromFilename($filename)
	{
		$parts 		= explode(".",$filename);
		$ext   		= strtolower($parts[count($parts)-1]);
		
		switch($ext){
			case "jpg" : return Zend_Registry::getInstance()->types->images->jpeg;
			case "gif" : return Zend_Registry::getInstance()->types->images->gif;
			case "png" : return Zend_Registry::getInstance()->types->images->png;
			case "pdf" : return Zend_Registry::getInstance()->types->nonimages->pdf;
			case "doc" : return Zend_Registry::getInstance()->types->nonimages->word;
			case "xls" : return Zend_Registry::getInstance()->types->nonimages->excel;
			case "ppt" : return Zend_Registry::getInstance()->types->nonimages->powerpoint;
			default    : return "unknown";
			
		}
		
	}
	
	protected function _getMimeType($field_name)
	{
		$filename	= strtolower($_FILES[$field_name]['name']);
		return $this->_getMimeTypeFromFilename($filename);
		
	}
	
	protected function _resolveExtention($mime_type,$filename){
		
		$parts = explode(".",$filename);
		$ext   = strtolower($parts[count($parts)-1]);
		
		switch($mime_type){
			case Zend_Registry::getInstance()->types->images->jpeg	   : return "jpg";
			case Zend_Registry::getInstance()->types->images->gif 	   : return "gif";
			case Zend_Registry::getInstance()->types->images->png 	   : return "png";
			case Zend_Registry::getInstance()->types->nonimages->pdf   : return "pdf";
			case Zend_Registry::getInstance()->types->nonimages->word  : return "doc";
			case Zend_Registry::getInstance()->types->nonimages->excel : return "xls";
			default	: return $ext;		
		}
				
	}
	
	private function _getCacheDirectory(){
		$pathname = realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_cache).DIRECTORY_SEPARATOR;
		if (strlen($pathname) == 1)
			throw new Exception("Cachedir (".Zend_Registry::getInstance()->paths->dir_cache.") bestaat niet.");
		
		return $pathname;
	}
	
	private function _getCachedImages($filename){
		
		$filename = basename($filename);
		$path     = $this->_getCacheDirectory();
		$files 	  = array();
		
		if ($dh = opendir($path)) {
        	while (($file = readdir($dh)) !== false) {        		
        		if ((is_file($path.$file)) && ( substr( $file, -strlen( $filename ) ) == $filename) )
        			$files[] = $file;
        	}
        	closedir($dh);
    	}
		
		return $files;
	}
	
	protected function _moveUploadedFile($form,$fieldname,$destination_path,$new_filename=NULL,$old_filename=NULL)
	{
		assert($fieldname !== NULL);
		assert($form !== NULL);	
		assert($destination_path !== NULL);	
		
		$new_filename = strtolower($new_filename);	// bestanden altijd met kleine letters
		
		// resolve destination path en check of het path bestaat
		
		$dest_path = realpath($destination_path).DIRECTORY_SEPARATOR;
		if ($dest_path == "\\"){
			$this->LogError("destination path (".$destination_path.") bestaat niet.");
			return false;
		}
		
		// check if destination path is writable
		
		if (!is_writable($dest_path)){
			$this->LogError("destination path ($dest_path) is niet writable.");
			return false;
		}
	
		$filename = $form->getElement($fieldname)->getFileName();
			
		if ( (isset($filename)) && (!empty($filename)) )
		{			
			$basename = basename($filename);
			if ($new_filename != NULL)
				$new_filename = $dest_path.$new_filename;
			else
				$new_filename = $dest_path.$basename;
				
			if ($old_filename != NULL)
			{
				$old_filename = $dest_path.$old_filename;
			
				if (file_exists($old_filename)){
					$this->Log("oude file $old_filename bestaal; verwijderen");
					$succeeded = unlink($old_filename);
				
					if(!$succeeded){
						$this->LogError("oude file $old_filename kan niet verwijderd worden");
					}
				}
			}
				
			if (file_exists($new_filename)){
			
				$this->Log("nieuwe file $new_filename bestaal al; verwijderen");
				$succeeded = unlink($new_filename);
				
				if(!$succeeded){
					$this->LogError("nieuwe file $new_filename kan niet verwijderd worden");
				}				
			}
			
			// cached images verwijderen
				
			$files = $this->_getCachedImages($new_filename);
			foreach($files as $file){
				$succeeded = unlink($this->_getCacheDirectory().$file);
			}
			
			$succeeded = rename($filename,$new_filename);			
						
			if(!$succeeded){
				$this->LogError("file kon niet geupload naar ".$dest_path);
			}
			else
				$this->Log("file $new_filename ge-upload");
			
			return basename($new_filename);
		}
		else
		{
			$this->LogError("filenaam niet gezet");
			return false;	
		}
	}
	
	protected function _deleteUploadedFile($model,$id,$field_filename,$field_mimetype,$field_orgfilename)
	{
		$data     = $model->fetchEntry($id);
		assert(isset($data['id']));
		assert(isset($data[$field_filename]));
		
		$filename = $data[$field_filename];
		
		if (($filename !== NULL) && ($filename != ""))
		{
			$filename = $this->_getUploadDirectory().DIRECTORY_SEPARATOR.$filename;
			$ret = unlink($filename);
		
			if ($ret == false)
				$this->LogError("verwijderen mislukt: file $filename bestaat niet.");
			else
				$this->Log("File $filename verwijderd.");
		}
		
		$ndata[$field_filename] = NULL;
		$ndata[$field_mimetype] = NULL;
		$ndata[$field_orgfilename] = NULL;
		
		$model->update($id,$ndata);
	}
	
	protected function _handleUpload($form,$id,$prefix,$old_filename,$filename_field,$filename_orgname_field,$filename_mimetype_field)
	{
		
		$data    		 = $form->getValues();
		
		$target_filename = $prefix.$id.".".$this->_resolveExtention($_FILES[$filename_field]['type'],$_FILES[$filename_field]['name']);

		$mime_type		 = $this->_getMimeType($filename_field);		
		
		$org_filename    = $data[$filename_field]; 
		$dest_path		 = $this->_getUploadDirectory();
		
		$filename        = $this->_moveUploadedFile($form,$filename_field,$dest_path,$target_filename,$old_filename);
		
		if ($filename !== false){
			$ndata[$filename_field] 	  	 = $filename;
			$ndata[$filename_orgname_field]  = $org_filename;
			$ndata[$filename_mimetype_field] = $mime_type;
		}
		else
		{
			$ndata[$filename_field] 	  	 = NULL;
			$ndata[$filename_orgname_field]  = NULL;
			$ndata[$filename_mimetype_field] = NULL;
		}
		
		return $this->_update($id,$ndata);	
	}
	
	
	protected function _hasRootPermission(){
		
		$user_group_data = $this->_getModel()->fetchRoleByUserId($this->_getCurrentUserId());

		if ($user_group_data['role'] == Zend_Registry::getInstance()->roles->roles->root->name){
			return true;
		}
		else 
			return false;
		
		
	}
	
}