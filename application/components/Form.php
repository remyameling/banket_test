<?php

class RAC_Component_Form extends Zend_Form 
{
	protected $_configFilename;
	protected $_formName;
	
	public function __construct($configFileName,$formName)
    {	
    	if ($configFileName !== NULL)
		{
			if (!file_exists(APPLICATION_PATH."/".$configFileName))
				throw new Exception("Formulier configuratie file (".APPLICATION_PATH."/".$configFileName.") niet gevonden.");
				
			$this->_configFilename = $configFileName;
			$this->_formName = $formName;
		
			$Formconfig	= new Zend_Config_Ini(APPLICATION_PATH."/".$configFileName, APPLICATION_ENV);			
			
			if ($Formconfig->get($formName) == NULL)
				throw new Exception("Formulier ".$formName." niet gevonden in ".APPLICATION_PATH."/".$configFileName);
				
			$this->addElementPrefixPath('My_Decorator',APPLICATION_PATH.'/components/decorators/','decorator'); 
			$this->addDisplayGroupPrefixPath('My_Decorator',APPLICATION_PATH.'/components/decorators/','decorator'); 
			
			parent::__construct($Formconfig->get($formName));
					
			$this->_getFormFromFile($Formconfig->get($formName));
			$this->_removeProtectedFields($Formconfig->get($formName),$this->_getRole());
			
			// add id of form as hidden field
			
			assert($this->getAttrib('id') !== NULL);	// form id is verplicht
			assert($this->getAttrib('id') != "");
						
			$idElement = new Zend_Form_Element_Hidden("frmid");
			$idElement->setValue($this->getAttrib('id'));

			$this->addElement($idElement);
		}
		else
			throw new Exception('Kan form ini file ('.$configFileName.') niet vinden');
    }
    
	protected function _getRole(){
    	return App_Auth_Auth::getInstance()->getRole();
    }
    
    public function InsertElementBefore($new_element,$element_name){
    	    	
    	$elements = $this->getElements();
    	Zend_Registry::getInstance()->logger->log("reset form", Zend_Log::NOTICE);
    	
    	$this->clearElements();
    	foreach($elements as $name => $element){
    		
    		if ($name == $element_name){
    			$this->addElement($new_element);
    			$this->addElement($element);
    		}    			
    		else{
    			$this->addElement($element);
    		}
    	}    	
    }	
    
    private function _removeProtectedFields($formConfig,$role)
    {	
    	foreach($formConfig->elements as $element_name => $element)
    	{
			if (!empty($element->access)){
				$bAccess = false;
				foreach($element->access as $key => $value){					
					if ($role == $value)
						$bAccess = true;
				}

				if (!$bAccess)
					$this->removeElement($element_name);
				
			}
			else
				$bAccess = true;
		}
		
    }
	
	private function _getFormFromFile($formConfig)
    {	
		foreach($formConfig->elements as $key => $element)
		{
			$this->getElement($key)->addPrefixPath("My_Validate",
													APPLICATION_PATH.'/components/validators',Zend_Form_Element::VALIDATE);
		
			if (isset($element->class))
			{
				$this->getElement($key)->setAttrib('class',$element->class);
			}
			if (isset($element->onoption))
			{
				$this->getElement($key)->setAttrib('onoption',$element->onoption->element."=".$element->onoption->value);
				
				$current_key = $this->getElement($element->onoption->element)->getAttrib("showelements");				
				
				if ($current_key != "")
					$new_key = $current_key.";".$key;
				else
					$new_key = $key;								
					
				$this->getElement($element->onoption->element)->setAttrib("showelements",$new_key);
			}
			if (isset($element->disabled))
			{
				$this->getElement($key)->setAttrib('disabled',$element->disabled);				
			}
			if (isset($element->configoptions))
			{
				if (isset($element->configoptions->inlabel))
					$label = $element->configoptions->inlabel;
				else
					$label = NULL;
					
				$this->_populateConfigoptions($this->getElement($key),
											 $element->configoptions->inifile,
											 $label,
											 $element->configoptions->key);
			}
			if (isset($element->multioptions))
			{
				if (isset($element->multioptions->inlabel))
					$label = $element->multioptions->inlabel;
				else
					$label = NULL;
					
				$this->_populateMultioptions($this->getElement($key),
											 $element->multioptions->model,
											 $label,
											 $element->multioptions->sort,
											 $element->multioptions->value_field,
											 $element->multioptions->label_field);
			}
			if (isset($element->multilevel))
			{
				if (isset($element->multilevel->inlabel))
					$label = $element->multilevel->inlabel;
				else
					$label = NULL;
					
				// create filter element
				
				$this->_populateMultioptions($this->getElement($key),
											 $element->multilevel->model,
											 $label,
											 $element->multilevel->sort,
											 $element->multilevel->value_field,
											 $element->multilevel->label_field,
											 $element->multilevel->filter->value);											 
															 
				$current_key = $this->getElement($element->multilevel->filter->element)->getAttrib("filter");				
				
				if ($current_key != "")
					$new_key = $current_key.";".$key;
				else
					$new_key = $key;								
					
				$this->getElement($element->multilevel->filter->element)->setAttrib("filter",$new_key);
			}
			if (isset($element->autocopy))
			{
				
				$this->getElement($key)->setAttrib('autocopy',$element->autocopy);	
			}
			if (isset($element->addoptions))
			{
				$this->_addOptions($this->getElement($key),$element->addoptions);
			}
			if (isset($element->multicheckbox))
			{
				if (isset($element->multicheckbox->inlabel))
					$label = $element->multicheckbox->inlabel;
				else
					$label = NULL;
					
				$this->_populateMultiCheckbox($this->getElement($key),$element->multicheckbox->model,$label,$element->multioptions->sort);
			}
			if (isset($element->options->validators))
			{
				$this->_addValidationMessage($this->getElement($key),$element);
			}
			if (isset($element->options->required))
			{
				if ($element->options->required == true)
				{
					$this->_addRequiredMessage($this->getElement($key),$element);
				}				
			}
			if (isset($element->destination))
			{
				$this->getElement($key)->setDestination(APPLICATION_PATH.Zend_Registry::getInstance()->paths->uploads);
			}			
		}
    }  
	
	private function _addValidationMessage($element,$config)
	{
		foreach($config->options->validators as $validator_name => $validator)
		{
			if (isset($validator->messages))
			{
				foreach($validator->messages as $key=>$message)
				{
					$code = $key;
					$text = $message;
					
					$validator = $element->getValidator($validator_name);
					if ($validator == NULL)
						throw new Exception("Validator not found in LWForm");
					$validator->setMessage($text,$code);					
				}
			}
		}
	}
	
	private function _addOptions($element,$options)
	{
		$opt_ar = array();
		foreach($options as $key=>$option)
			$opt_ar[$key] = $option;

		$element->addMultiOptions($opt_ar);
	}	
	
	private function _addRequiredMessage($element,$config)
	{
		$msg = $config->options->requiredmessage;
		if ($msg != NULL)
		{
			$element->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $msg)));		
		}
	}	
	
	private function _populateConfigoptions($element,$inifile,$inline_label,$main_inikey)
	{
		assert($inifile != NULL);
		assert($element != NULL);
		
		$element_name = $element->getName();
		
		if (Zend_Registry::getInstance()->isRegistered($inifile))
		{
			$cfg 	  = Zend_Registry::getInstance()->get($inifile);
			$keys     = $cfg->get($main_inikey);
			
			if ($keys !== NULL){
				
				$key      = $keys->get($element_name);
				if ($key !== NULL)
					$entries  = $key->toArray();
				else
					throw new Exception("subkey '$element_name' van '$main_inikey' niet aanwezig in config file '$inifile'");				
			}
			else
				throw new Exception("key '$main_inikey' niet aanwezig in config file '$inifile'");			
		}
		else
			throw new Exception("inifile ($inifile) is niet geregistreerd.");
		
		$options = array();
			
		if ($inline_label != NULL)
			$options[0] = $inline_label;
				
		foreach($entries as $key=>$value)
			$options[$key] = $value;
			
		$element->addMultiOptions($options);			

	}
	
	private function _populateMultioptions($element,$model_name,$inline_label,$sort,$value_field="",$label_field="",$filter_field="")
	{
		assert($model_name != NULL);
		assert($element != NULL);
		
		if ($value_field == "")
			$value_field = 'id';
		if ($label_field == "")
			$label_field = NULL;
		if ($filter_field == "")
			$filter_field = NULL;

		    $model_name = "App_Model_".ucwords($model_name);				
			$model      = new $model_name();
			$table_name = "App_Model_DBTable_".ucwords($model_name);
			
			$entries = $model->fetchEntries($sort);
			
			$options = array();
			
			if ($inline_label != NULL)
				$options[0] = $inline_label;
				
			
			
			foreach($entries as $entry)
			{
				$option = array();
				
				$value     = $entry[$value_field];	
				unset($entry[$value_field]);
				
				if ($filter_field != NULL)
					$value = $value."#".$entry[$filter_field];
								
				if ($label_field == NULL) // if label field not set, get the first field as the label
				{
					$labels  = array_values($entry);
					$label   = $labels[0];
				}
				else
				{
					$label = $entry[$label_field];
				}

				//$options[$value] = htmlspecialchars_decode(htmlentities($label,ENT_QUOTES));				
				$options[$value] = $label;
				
			}
			
			$element->addMultiOptions($options);			

	}
	
	private function _populateMultiCheckbox($element,$model_name,$inline_label)
	{
		assert($model_name != NULL);
		assert($element != NULL);
		
		$filename = Zend_Registry::getInstance()->model->models->get($model_name);
		if ($filename)
		{
			$filename = APPLICATION_PATH . Zend_Registry::getInstance()->model->models->get($model_name);
			require_once $filename;
			
			if (Zend_Registry::getInstance()->configuration->domain->get($model_name) === NULL)
				throw new Exception("Geen entry gevonden voor domain.".$model_name.".model");
						
			$modelClass = new ReflectionClass(Zend_Registry::getInstance()->configuration->domain->get($model_name)->model);
			$table_name = Zend_Registry::getInstance()->configuration->domain->get($model_name)->table;
			$model 		= $modelClass->newInstance($model_name,$table_name);		
			
			$entries = $model->fetchEntries($sort);
			$options = array();
			
			if ($inline_label != NULL)
				$options[0] = $inline_label;
				
			foreach($entries as $entry)
			{
				$option = array();
				$id     = $entry['id'];
	
				unset($entry['id']);
				$value  = array_values($entry);
				$options[$id] = htmlspecialchars_decode(htmlentities($value[0],ENT_QUOTES));
			}
			$element->addMultiOptions($options);			
		}
		else
			throw new Exception("Filename voor model (".$model_name.") niet gevonden in configuratie file");
	}	
	
	public function setDefaults($data)
	{
		return parent::setDefaults($data);		
	}
		
}