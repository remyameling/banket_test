<?php

require_once "BaseController.php";

class Admin_BackupController extends Admin_BaseController
{
	private $msg = "";
	
	private function line($text,$num_tabs=0)
	{
		$tabs="";
		for($i=0;$i<$num_tabs;$i++)
			$tabs .= "  ";
			
		return $tabs.$text."\r\n";
	}
	
	private function getTableStructureFields($tablename)
	{
		$fields 	= Zend_Registry::getInstance()->dbAdapter->fetchAll("SHOW FIELDS FROM `".$tablename."`");
		
		$out 		= "";
		
		foreach($fields as $field)
		{
			$def = $field['Default'];
			if ($def != 'CURRENT_TIMESTAMP')
				$def = "'$def'";
			
			
			$default = ($field['Default'] != "") ? " default $def" : "";
			$null 	 = ($field['Null'] != "YES") ? " NOT NULL" : "";
			
			if (($field['Null'] == "YES") && ($field['Default'] === NULL))
				$default = " default NULL";
			
            $extra 	 = ($field['Extra'] !="") ? " ".$field['Extra'] : "";
            
            $out    .=  $this->line("`".$field['Field']."` ".$field['Type'].$null.$default.$extra.",",1);
			
			
		}
		return $out;
	}
	
	private function getKeys($tablename)
	{
		$keys	= Zend_Registry::getInstance()->dbAdapter->fetchAll("SHOW KEYS FROM `".$tablename."`");
		$x   	= 0;
		
		foreach($keys as $key)
		{
			if($key['Non_unique'] == 1 && $key['Key_name'] != "PRIMARY" && $key['Index_type']!="FULLTEXT")
			{
            	$knaam = "UNIQUE";
            }
            elseif($key['Non_unique'] == 1 && $key['Key_name'] != "PRIMARY" && $key['Index_type']=="FULLTEXT")
            {
            	$knaam = "FULLTEXT";
            }
            elseif($key['Non_unique'] == 0 && $key['Key_name'] != "PRIMARY" && $key['Index_type']=="BTREE")
            {
            	$knaam = "UNIQUE";
            }
            elseif($key['Key_name'] == "PRIMARY")
            {
            	$knaam = "PRIMARY";
            }
            else
            {
            	$knaam = "KEY";
            }
          	
            $index[$key['Key_name']]['type'] = $knaam;
            $index[$key['Key_name']]['columns'][$key['Seq_in_index']] = $key['Column_name'];
		}
		
		
		return $index;
	}
	
	private function getIndexString($type,$name,$columns)
	{
		if (count($columns) == 1)
		{
			if ($type == 'PRIMARY')
				$out = $type." KEY  (`".$columns[1]."`),";
			else
				$out = $type." KEY `$name` (`".$columns[1]."`),";
		}
		else
		{
			
			$out = $type." KEY `$name` (";
			foreach($columns as $column){
				$out .= "`$column`,";	
			}
			
			$out  = substr($out,0,strlen($out)-1);			
			$out .= "),";
		}
		return $this->Line($out,1);
	}
	
	private function getTableStructureKeys($tablename)
	{
		$indexes = $this->getKeys($tablename);
		$out  	 = "";
		
		foreach($indexes as $index_name=>$index){
			$out .= $this->getIndexString($index['type'],$index_name,$index['columns']);
		}
		
		$out = substr($out,0,strlen($out)-3)."\r\n";
		
		return $out;
	}
	
	private function getMetaData($schema,$table)
	{
		$data = Zend_Registry::getInstance()->dbAdapter->fetchAll("SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME = '$table'");
		
		return $data[0];
		
	}
	
	private function getTableStructure($schemaname,$tablename,$default_charset,$bAddDropTable,$bAddIfNotExists)
	{
		$metadata = $this->getMetaData($schemaname,$tablename);
		$engine   = $metadata['ENGINE'];
		$autoic   = $metadata['AUTO_INCREMENT'];
		
		
		$out    =   $this->line("--");
		$out   .=   $this->line("-- Table structure for table `".$tablename."`");
		$out   .=   $this->line("--");
		$out   .=   $this->line("");
		
		if ($bAddDropTable)
			$out   .=   $this->line("DROP TABLE IF EXISTS `".$tablename."`;");
			
		if ($bAddIfNotExists)
			$out   .=   $this->line("CREATE TABLE IF NOT EXISTS `".$tablename."` (");
		else
			$out   .=   $this->line("CREATE TABLE `".$tablename."` (");
			
		$out   .=   $this->getTableStructureFields($tablename);
		$out   .=   $this->getTableStructureKeys($tablename);
		
		if ($autoic != "")
			$out   .=   $this->line(") ENGINE=$engine  DEFAULT CHARSET=$default_charset AUTO_INCREMENT=$autoic ;");
		else
			$out   .=   $this->line(") ENGINE=$engine  DEFAULT CHARSET=$default_charset ;");
			
		$out   .=   $this->line("");
		$out   .=   $this->line("-- --------------------------------------------------------");
		$out   .=   $this->line("");	

		return $out;
	}
	
	public function getStructure($schema,$default_charset,$bAddDropTable,$bAddIfNotExists)
	{
		$tables	= Zend_Registry::getInstance()->dbAdapter->fetchAll("SHOW TABLES");
    	$out 	= "";
    	
		foreach($tables as $table)
    	{
    		$keys 		= array_keys($table);
    		$table_name = $table[$keys[0]];  		
    	
    		$out	   .= $this->getTableStructure($schema,$table_name,$default_charset,$bAddDropTable,$bAddIfNotExists);    		
    	}
    	
    	return $out;
	}
	
	private function getFieldString($fields)
	{
		$out = "(";
		foreach($fields as $field)
			$out .= "`".$field['Field']."`,";
		
		$out = substr($out,0,strlen($out)-1);
		$out .= ")";
		
		return $out;
	}
	
	private function getSimpleType($type)
	{
		$pos = strpos($type,"(");
		if ($pos == false)
			return $type;
		else
			return substr($type,0,$pos);	
	}
	
	private function getStringValue($value)
	{
		$value = str_replace("'","''",$value);
				
		return "'$value'";
	}
	
	private function getValue($type,$value)
	{
		if ($value === NULL)
			return "NULL";
			
		$type = $this->getSimpleType($type);
		
		switch($type){
			case 'int':
			case 'tinyint':
			case 'binary':
				return (int)$value;
			case 'varchar':
			case 'longtext':
			case 'datetime':
			case 'date':
			case 'time':
			case 'timestamp':
				return $this->getStringValue($value);
			default:
				die("unknown type: $type");
		}
	}
	
	private function getRecordString($fields,$record)
	{
		$out = "(";
		foreach($fields as $field)
		{
			$out .= $this->getValue($field['Type'],$record[$field['Field']]).", ";
		}
		
		$out = substr($out,0,strlen($out)-2);
		$out .= "),";
		
		return $out;
	}
	
	public function getTableData($tablename)
	{
		$fields  = Zend_Registry::getInstance()->dbAdapter->fetchAll("SHOW FIELDS FROM `".$tablename."`");
		$content = Zend_Registry::getInstance()->dbAdapter->fetchAll("SELECT * FROM `".$tablename."`");
		
		$out    =   $this->line("--");
		$out   .=   $this->line("-- Dumping data for table `".$tablename."`");
		$out   .=   $this->line("--");
		$out   .=   $this->line("");
		
		if (count($content) > 0)
		{
			$fieldstring = $this->getFieldString($fields);
			
			$out   .= $this->Line("INSERT INTO `".$tablename."` ".$fieldstring. " VALUES");
			
			foreach($content as $record){
				
				$recordstring = $this->getRecordString($fields,$record);
				
				$out   .= $this->Line($recordstring);
			}
			
			$out = substr($out,0,strlen($out)-3).";\r\n\r\n";
		}
		
		
		
		return $out;
		
	}
	
	public function getData()
	{
		$tables	= Zend_Registry::getInstance()->dbAdapter->fetchAll("SHOW TABLES");
    	$out 	= "";
    	
    	
		foreach($tables as $table)
    	{
    		$keys 		= array_keys($table);
    		$table_name = $table[$keys[0]];  		
    	
    		$out	   .= $this->getTableData($table_name);    		
    	}
    	return $out;
	}
	
	public function saveFile($filename,$text)
	{
		$fp = fopen($filename, "w");
	    fwrite($fp, $text);
	    fclose($fp);		
	}
	
	public function zipFile($filename,$zipfilename,$localname,$deleteOrgFile=true)
	{
		$zip = new ZipArchive();
		

		if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {
    		exit("cannot open <$filename>\n");
		}
		else
		{
			$zip->addFile($filename,$localname);
			$zip->close();
			
			unlink($filename);
		}
	}
	
	public function unzipFile($directory,$zipfilename,$filename)
	{
		$zip = new ZipArchive();
		
		if ($zip->open($directory.$zipfilename)!== TRUE)
		{
    		exit("cannot open <$zipfilename>\n");
		}
		else
		{
			$zip->extractTo($directory,$filename);
			$zip->close();
			
			return true;
		}
	}
	
	public function createAction()
    {
    	$name	 		= $this->_getOptionalParam("name",$_SERVER['HTTP_HOST']);
    	    	
    	$schema 		= Zend_Registry::getInstance()->site->resources->db->params->username;
    	$directory  	= realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup).DIRECTORY_SEPARATOR;
    	
    	$date 			= date("Ymd_Hms");
    	$name			= str_replace(".","-",$name);
    	$name			= str_replace(" ","-",$name);
    	    	
    	$filename   	= $date."_".$name.".full.sql";
    	$zipfilename	= $date."_".$name.".full.zip";
    	
    	$out    = $this->Line("-- RACCMS SQL Dump");
    	$out   .= $this->Line("--");
    	$out   .= $this->Line("-- Ap version ".Zend_Registry::getInstance()->consts->application_version);
    	$out   .= $this->Line("-- Db version ".Zend_Registry::getInstance()->consts->db_version);
    	$out   .= $this->Line("--");
    	$out   .= $this->Line("-- Host: ".$_SERVER['HTTP_HOST']);
    	$out   .= $this->Line("-- Generation Time: ".date("d-m-Y h:m:s"));
    	$out   .= $this->Line("");
    	$out   .= $this->Line('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');
    	$out   .= $this->Line("");
    	
    	
    	$out   .= $this->Line("--");
    	$out   .= $this->Line("-- Database: `$schema`");
    	$out   .= $this->Line("--");
    	$out   .= $this->Line("");
    	$out   .= $this->line("-- --------------------------------------------------------");
    	$out   .= $this->Line("");
    	
    	$out   .= $this->getStructure($schema,'latin1',true,true);
    	$out   .= $this->getData();
    	
    	
    	$this->saveFile($directory.$filename,$out);
    	$this->zipFile($directory.$filename,$directory.$zipfilename,$filename);
    }
    
    public function deleteAction()
    {
    	$filename 	= $this->_getRequiredParam('file');
    	$confirmed 	= $this->_getOptionalParam('confirm',false);   	
    	$directory	= realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup).DIRECTORY_SEPARATOR;
    	
    	$this->view->confirmed = false;
    	$this->view->filename  = $filename;
    	
    	if (file_exists($directory.$filename)) 
    	{
    		if ($confirmed == 'true')
    		{
    			unlink($directory.$filename);
    			$this->view->confirmed = true;
    		}
    		   		
    		
    		$this->view->found = true;
    	}
    	else
    		$this->view->found = false;
    }
    
    public function restoreAction()
    {
    	$filename 	= $this->_getRequiredParam('file');
    	$confirmed 	= $this->_getOptionalParam('confirm',false);
    	$directory	= realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup).DIRECTORY_SEPARATOR;
    	
    	
    	
    	if ((file_exists($directory.$filename)) && ($confirmed == 'true'))
    	{
    		$path_info 		= pathinfo($directory.$filename);
    		$sql_filename	= $path_info['filename'].".sql";
    		
    		$res = $this->unzipFile($directory,$filename,$sql_filename);
    		
    		if ($res)
    		{
    			
    			
    			
    			$res  = Zend_Registry::getInstance()->dbAdapter->fetchAll("SHOW VARIABLES LIKE 'max_allowed_packet'");
    			$max  = $res[0]['Value'];
    			
    			$sql  = file_get_contents($directory.$sql_filename);
    			$left = $sql;
				$pos  = strpos($left,"INSERT INTO",0);

				while($pos > 0)
				{
					$statements = substr($left,0,$pos);
					
					$res  = Zend_Registry::getInstance()->dbAdapter->getConnection()->exec($statements);
					assert($res !== false); 
					
					$left = substr($left,$pos);
					$pos  = strpos($left,"INSERT INTO",1);
				}
    			
				if (strlen($left) > 0)
				{
					$res  = Zend_Registry::getInstance()->dbAdapter->getConnection()->exec($left);
					assert($res !== false); 
				}
					 
    			unlink($directory.$sql_filename);
    			
    			
    			$this->view->succeeded = true;
    		}
    		else
    		{
    			$this->view->succeeded = false;
    		}
    	}
    	
    	$this->view->confirmed = $confirmed;
    	$this->view->filename  = $filename;
    }
    
	public function mailAction()
    {
    	$filename 	= $this->_getRequiredParam('file');
    	$directory	= realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup).DIRECTORY_SEPARATOR;
    	
    	if (file_exists($directory.$filename)) 
    	{
    		$helper = new App_Mailsender();
    		
    		$attachment['type']		= 'application/zip';
    		$attachment['data'] 	= file_get_contents($directory.$filename);
    		$attachment['filename'] = $filename;
    		
    		
    		$helper->sendMail(Zend_Registry::getInstance()->appmail->backup->toArray(),
							  Zend_Registry::getInstance()->db_settings->toArray(),
							  $attachment,NULL);
							  					  
			$this->view->sent = true;
							  					  
			$this->view->emailaddress = Zend_Registry::getInstance()->db_settings->default_admin_email;
    	}
    	else
    		$this->view->sent = false;
    }

    public function indexAction()
    {
    	$directory	= realpath($this->_getTemplateBase().Zend_Registry::getInstance()->paths->dir_backup).DIRECTORY_SEPARATOR;
    	$files      = array();
    	
    	if ($handle = opendir($directory))
    	{
    		while (false !== ($file = readdir($handle)))
		    {
		    	$path_info = pathinfo($directory.$file);
		    	
		    	if (($file != ".") && ($file != ".."))// && ($path_info['extension'] == "zip"))
		    	{
		    		$parts = explode("_",$file);
		    		$names = explode(".",$parts[2]);
		    		
		    		
		    		
		    		$files[$file] = substr($parts[0],0,4)."-".substr($parts[0],4,2)."-".substr($parts[0],6,2).
		    					    " (".
		    					    substr($parts[1],0,2).":".substr($parts[1],2,2).":".substr($parts[1],4,2).
		    					    "): ".
		    					    str_replace("-"," ",$names[0]);
		    		
		    	}
		    }
			closedir($handle);
    	}
    	
    	$this->view->files = $files;
    }
}