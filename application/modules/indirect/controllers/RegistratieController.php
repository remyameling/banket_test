<?php

require_once "BaseController.php";

class Indirect_RegistratieController extends Indirect_BaseController
{
	protected $_model 			= NULL;
	protected $_flashmessenger  = NULL;
	protected $_domain_name 	= "registratie";
	
	public function init()
    {
    	$this->_flashmessenger	= $this->_helper->getHelper('FlashMessenger');
     
    	$this->initLayout(Zend_Registry::getInstance()->paths->layout->default->default,
						  $this->_getTemplateBase().Zend_Registry::getInstance()->paths->layouts_templates);
						  
        return parent::init();
    }
    
    private function _toDateTime($time){
    	
    	$ret = date("Y-m-d h:m:s");
    	if (substr($time,0,1) == '>'){
    		$time 	= substr($time,1);
    		$ret 	= date("Y-m-d h:m:s",strtotime("+1 day"));
    	}
    	
    	$ret = substr($ret,0,11).$time.":00";    	
    	return $ret;
    	
    }
    
    protected function _handleRegistration($form)
    {
    	$data 		= $form->getValues();
    	$controller = $this->_getRequiredParam('controller');
    	$module		= $this->_getRequiredParam('module');
    	$dienst_id	= $this->_getRequiredParam('dienst');
    	$datum		= $this->_getRequiredParam('datum');
    	$functies   = Zend_Registry::getInstance()->sites->functie->get($this->_getCurrentSiteName())->toArray();
    	
    	unset($data['frmid']);
    	
    	// aantal converteren naar integers
    	
    	foreach($functies as $functie_id=>$functie){
    		if (!empty($data['functie_'.$functie_id])){
    			$aantalf = $data['functie_'.$functie_id];    			
    			$aantalf = (string) str_replace(".","@",$aantalf);
				$aantalf = (string) str_replace(",",".",$aantalf);
				$aantalf = (string) str_replace("@",",",$aantalf);
		
				// rond af op 1 decimaal en converteer naar integer		
		
				$aantalf 					  = (float)  round($aantalf,2);
				$data['functie_'.$functie_id] = (int) 100*$aantalf;				
    		}
    	}
    	
    	// ophalen oude data voor deze datum,dienst en site; indexen vervangen door functie_id's
    	$old_data 			= App_ModelFactory::getModel('indirect')->fetchByDateAndDienst($datum,$dienst_id,$this->_getCurrentSiteId());
    	foreach($old_data as $old)
    		$reg[$old['functie_id']] = $old;
    		
    	foreach($functies as $functie_id=>$functie){									// voor alle mogelijke funkties    		
    		if (!empty($data['functie_'.$functie_id])){									// als er een registratie is ingevuld voor deze funktie    			
    			$aantal 				= $data['functie_'.$functie_id];					// aantal FTE voor deze functie			
    							
    			if (isset($reg[$functie_id])){												// indien er al een record was voor deze funktie    				
    				$id          = $reg[$functie_id]['id'];
    				$update_data = array('aantal_fte'=>$aantal);
    				App_ModelFactory::getModel('indirect')->update($id,$update_data);		// dan update het record
    				
    			}	
    			else{																	    				
    				$savedata['functie_id'] = $functie_id;
    				$savedata['aantal_fte'] = $aantal;
    				$savedata['datum'] 		= $datum;										
    				$savedata['dienst_id']	= $dienst_id;
    				$savedata['site_id'] 	= $this->_getCurrentSiteId();
    			
    				
    				App_ModelFactory::getModel('indirect')->save($savedata);				// zo niet, dan save nieuw record
    			}
    		}
    		else{																		// zo niet 
    			if (isset($reg[$functie_id])){												// als er wel een record was voor deze funktie    				
    				App_ModelFactory::getModel('indirect')->delete($reg[$functie_id]['id']);// dan verwijder het
    			}	
    		}
    	}
    	
    	
    	$this->_flashmessenger->addMessage('Registratie opgeslagen !');
	    	
    	$this->_redirector->gotoSimple('index','index',$module,array());			
    }
    
	protected function _initRegistratieForm($form)
    {
    	// bepaal functies voor de huidige site
    	$functies   = Zend_Registry::getInstance()->sites->functie->get($this->_getCurrentSiteName())->toArray();
    	
    	$submit = $form->getElement('save');
    	$frmid	= $form->getElement('frmid');
    	$form->removeElement('save');
    	$form->removeElement('frmid');
    	
    	foreach($functies as $idx=>$naam){
    		
    		$betweenValidator = new Zend_Validate_Between(array('min'=>0,'max'=>999));
    		$floatValidator   = new Zend_Validate_Float(array('locale'=>'de'));
    		$validators       = array($betweenValidator,$floatValidator);
    		
    		$element = new Zend_Form_Element_Text('functie_'.$idx,array('label'=>$naam,'validators'=>$validators,'required'=>false,'size'=>5));    		
    		$form->addElement($element);    		
    	}
    	
    	
    	$form->addElement($submit);
    	$form->addElement($frmid);
    	
    	return $form;
    }
    
    public function indexAction()
    {
    	$form 		= $this->_getForm('registratie');
    	$form 		= $this->_initRegistratieForm($form);
    	
    	$dienst_id	= $this->_getRequiredParam('dienst');
    	$datum		= $this->_getRequiredParam('datum');
    	
    	$data = App_ModelFactory::getModel("Indirect")->fetchByDateAndDienst($datum,$dienst_id,$this->_getCurrentSiteId()); 
    	
    	
    	$dat['datum'] 	= $datum;
    	$dat['dienst']	= Zend_Registry::getInstance()->sites->dienst->get($this->_getCurrentSiteName())->naam->get($dienst_id);
    	
    	foreach($data as $rec)    		
    		$dat['functie_'.$rec['functie_id']] = number_format($rec['aantal_fte']/100,2,",",".");
    	

    	//$this->p($dat);
    	
    	$ret  = $this->_handleForm($form,$dat,"_handleRegistration");
    }
}