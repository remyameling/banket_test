<?php

class App_Mailsender
{
	private function mailHtmlToText($html)
	{
		$text = strip_tags($html);
		$text = str_replace("&euro;",'EURO',$text);
		$text = str_replace("&nbsp;",' ',$text);
		$text = trim($text);
		
		return $text;
	}
	
	private function mailReplace($text,$replacement)
	{
		if (!is_array($text)){
			foreach($replacement as $string => $replacement_string)
			
				if (!is_array($replacement_string))			
					$text = str_replace("%".$string."%",$replacement_string,$text);
						
			return $text;
		}
	}
	
	protected function _getTemplateBase(){
		
		return (APPLICATION_PATH."/".
    			Zend_Registry::getInstance()->paths->templates_base.
    			WEBSITE.
    			Zend_Registry::getInstance()->paths->mail_templates.
    			DIRECTORY_SEPARATOR);
	}

	public function sendMail($config,$replacement_data,$attachment=NULL,$template_filename=NULL)
	{
		if ($config instanceof Zend_Config)
			$config = $config->toArray();
			
		if ($replacement_data instanceof Zend_Config)
			$replacement_data = $replacement_data->toArray();
		
		if ($template_filename !== NULL){	// template filename provides, overruled $config['template']
			
			$filename = realpath($this->_getTemplateBase().$template_filename);
			if (file_exists($filename))
				$config['template'] = file_get_contents($filename);
			else
				throw new Exception("RACCMS_Component_Mailsender::sendMail(): template file: $template_filename niet gevonden.");	
			
		}
		
		if ($config === NULL)
			throw new Exception("MailSender::sendMail: config param is NULL");
			
		
			
		$subject 	= $this->mailReplace($config['subject'],	$replacement_data);		
		$from    	= $this->mailReplace($config['from'],		$replacement_data);
		$fromAlias  = $this->mailReplace($config['fromalias'],	$replacement_data);
		$to	    	= $this->mailReplace($config['to'],			$replacement_data);
		$toAlias  	= $this->mailReplace($config['toalias'],	$replacement_data);
		$cc	    	= $this->mailReplace($config['cc'],			$replacement_data);
		$ccAlias  	= $this->mailReplace($config['ccalias'],	$replacement_data);
		$bcc	   	= $this->mailReplace($config['bcc'],		$replacement_data);
		$html 	 	= $this->mailReplace($config['template'],	$replacement_data);
		
		$test = $config['test'];
		
		if ($test)
		{
			echo '<table style="width:100%;">';
			echo '<tr><td style="vertical-align: top;">Formulier data</td><td><pre>'; print_r($replacement_data); echo "</pre></td></tr>";
			echo "<tr><td>Subject</td><td><pre>$subject</pre></td></tr>";
			echo "<tr><td>To</td><td><pre>$to ($toAlias)</pre></td></tr>";
			echo "<tr><td>From</td><td><pre>$from ($fromAlias)</pre></td></tr>";
			echo "<tr><td>Cc</td><td><pre>$cc ($ccAlias)</pre></td></tr>";
			echo "<tr><td>Bcc</td><td><pre>$bcc</pre></td></tr>";
			echo '<tr><td style="vertical-align: top;">HTML mail</td><td><pre>'.$html.'</pre></td></tr>';
			echo '<tr><td style="vertical-align: top;">text mail</td><td><pre>'.$this->mailHtmlToText($html).'</pre></td></tr>';
			die();
		}
		else
		{		
			$mail = new Zend_Mail();
			$mail->setBodyHtml($html);
			$mail->setBodyText($this->mailHtmlToText($html));
			$mail->setFrom($from,$fromAlias);
			$mail->setSubject($subject);
			
			if ($attachment !== NULL){
				
				$at 			 = $mail->createAttachment($attachment['data']);
				$at->type        = $attachment['type'];
				$at->disposition = Zend_Mime::DISPOSITION_INLINE;
				$at->encoding    = Zend_Mime::ENCODING_BASE64;
				$at->filename    = $attachment['filename'];
			}
						
			if ($toAlias != "")
				$mail->addTo($to,$toAlias);
			else
				$mail->addTo($to);
				
			if ($cc != "")
				$mail->addCc($cc);
				
			if ($bcc != "")
				$mail->addBcc($bcc);
				
			$mail->send();
		}		
	}
	
	public function getMailBody($config,$replacement_data)
	{
		if ($config === NULL)
			throw new Exception("MailSender::sendMail: config param is NULL");
			
		$html 	 	= $this->mailReplace($config['template'],	$replacement_data);
		
		return $html;		
	}
	
	public function getTo($config,$replacement_data)
	{
		if ($config === NULL)
			throw new Exception("MailSender::sendMail: config param is NULL");
			
		$to	    	= $this->mailReplace($config['to'],			$replacement_data);
				
		return $to;		
	}
	
	public function getSubject($config,$replacement_data)
	{
		if ($config === NULL)
			throw new Exception("MailSender::sendMail: config param is NULL");
			
		$subject 	= $this->mailReplace($config['subject'],	$replacement_data);	
				
		return $subject;		
	}
}