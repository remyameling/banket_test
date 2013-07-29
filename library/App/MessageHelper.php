<?php

class App_MessageHelper
{
	const VIA_TEXT 				= " via ";
	const MSG_LONG_MAXLENGTH 	= 500;
	const MSG_SHORT_MAXLENGTH 	= 140;
	
	static $_instance = NULL;
	
	private $add_merge_data     = array();
	
	public function setAddtionalMergeData($data)
	{
		$this->add_merge_data = $data;
	}
	
	public static function getInstance()
	{
		if (empty(self::$_instance))
		{
			self::$_instance = new App_MessageHelper();
		}
		return self::$_instance;			
	}
	
	private function HtmlToText($html)
	{
		$text = strip_tags($html,"<a>");
		$text = str_replace("&euro;",'EURO',$text);
		$text = str_replace("&nbsp;",' ',$text);
		$text = trim($text);
		
		return $text;
	}
	
	private function textReplace($text,$replacement)
	{
		if (!is_array($text)){
			foreach($replacement as $string => $replacement_string)
			
				if (!is_array($replacement_string))			
					$text = str_replace("%".$string."%",$replacement_string,$text);
						
			return $text;
		}
	}
	
	private function shortenUrls($text)
	{
		$tinyurl = new Zend_Service_ShortUrl_IsGd();
		
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		if(preg_match_all($reg_exUrl, $text, $url)){
			if (!empty($url)){
				foreach($url[0] as $url){
					
					
					
					$shortUrl = $tinyurl->shorten($url);
					$text     = str_replace($url,$shortUrl,$text);					
				}
			}
		}
		return $text;		
	}
	
	private function getMemberData()
	{
		$memberMdl  		= new App_Model_Member();
		$memberNetworkMdl   = new App_Model_Membernetwork();
		
		// get role and id of current user
		$role = App_Auth_Auth::getInstance()->getRole();
		$id   = App_Auth_Auth::getInstance()->getIdentityId();
		
		// get member data
		if ($role == 'friends')
		{
			$friendMdl = new App_Model_Friend();
			$data      = $friendMdl->fetchEntry($id);
			assert(!empty($data));
			
			$member_id 	 = $data['member_id'];
			$member_data = $memberMdl->fetchEntry($member_id);			
		}
		else
			$member_data = $memberMdl->fetchByUserId($id);
			
		assert(!empty($member_data));
		
		$memNetwData = $memberNetworkMdl->fetchByMember($member_data['id']);
		if (!empty($memNetwData)){
			foreach($memNetwData as $rec){
				$netw_id = $rec['network_id'];
				$dat['membernetwork_username_'.$netw_id] =  $rec['membernetwork_username'];
				$dat['membernetwork_url_'.$netw_id]      =  $rec['membernetwork_url'];
			}
		}
		else
			$dat = array();
			
		return array_merge($member_data,$dat);
	}
    
    public function prepareMsg($msg,$bPlainText=true,$bShortUrls=true)
    {
    	$replacement_data = array('kantoornaam_url'=>'<a href="http://www.grensstreekmakelaardij.nl">DEWonen</a>',
    							  'kantoornaam'=>'DEWonen',
    							  'kantoor_url'=>'http://www.grensstreekmakelaardij.nl');
    	
    	$settings_data    = Zend_Registry::getInstance()->db_settings->toArray();
    	$member_data	  = $this->getMemberData();
    	
    	
    	
    	
    	$data             = array_merge($replacement_data,$settings_data,$member_data,$this->add_merge_data);    	
    	$msg              = $this->textReplace($msg,$data);
    	if ($bShortUrls)
    		$msg		  = $this->shortenUrls($msg);
    	
    	if ($bPlainText)
    		$msg = $this->HtmlToText($msg);
    	
    	return $msg;    	
    }
    
    public function getLong($msgLong,$msgShort,$bIncludeVia=true,$bPlainText=true,$bShortUrls=false)
    {
    	if ($bIncludeVia)
    	{
    		$msgLong  = $this->prepareMsg($msgLong.
    									  self::VIA_TEXT.
    									  Zend_Registry::getInstance()->db_settings->website_name,$bPlainText,$bShortUrls);
    									  
    		if (strlen($msgLong) > self::MSG_LONG_MAXLENGTH)
    			$msgLong = $this->prepareMsg($msgShort.
    										 self::VIA_TEXT.Zend_Registry::getInstance()->db_settings->website_name,
    										 $bPlainText,$bShortUrls);
    	}
    	else
    	{
    		$msgLong  = $this->prepareMsg($msgLong,$bPlainText,$bShortUrls);
    		if (strlen($msgLong) > self::MSG_LONG_MAXLENGTH)
    			$msgLong = $this->prepareMsg($msgShort,$bPlainText,$bShortUrls);
    	}
    	
    	return $msgLong;
    }
    
	public function getShort($msgLong,$msgShort,$bIncludeVia=true,$bPlainText=true,$bShortUrls=true)
    {
    	$short  = $this->prepareMsg($msgLong,$bPlainText,$bShortUrls);		// try long text
    	if (strlen($short) > self::MSG_SHORT_MAXLENGTH)						// indien te lang
    		$short = $this->prepareMsg($msgShort,$bPlainText,$bShortUrls);	// get short text
    		
    	if ($bIncludeVia)
    	{
    		// indien mogelijk, plak via App text erachter
    		
    		$viaText = self::VIA_TEXT.Zend_Registry::getInstance()->db_settings->website_name;
    		$length  = strlen($viaText);
    		
    		if ( strlen($short)+strlen($viaText) <= self::MSG_SHORT_MAXLENGTH )
    			$short .= $viaText;
    	}
    		
    	if (strlen($short) > self::MSG_SHORT_MAXLENGTH)
    		return substr($short,0,self::MSG_SHORT_MAXLENGTH);
    	else
    		return $short;
    }
}