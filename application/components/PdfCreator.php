<?php

class RACCMS_Component_PdfCreator
{
	protected $_logGroup 	= "ADMIN";
	
	private   $template = NULL;
	private   $data     = NULL;
	private   $config   = NULL; 
	
	private function LogMsg($msg,$group,$prio){
		
		if (is_array($msg))
			$msg = implode(",",$msg);
    	
    	if (Zend_Registry::getInstance()->logging->groups->get($group)){   		
    		
    		$function = "";
    		$class    = "";
    		
    		if (Zend_Registry::getInstance()->logging->logcaller){
    	
	    		$trace=debug_backtrace();
			
				$caller=array_shift($trace);
				$caller=array_shift($trace);
				$caller=array_shift($trace);
	
				$function = $caller['function'];
				if (isset($caller['class']))
					$class = $caller['class']."::";
    		}
				
			Zend_Registry::getInstance()->logger->log($group.":".$class.$function." ".$msg,$prio);
    	}    	  	
    }
    
	protected function Log($msg)			// Debug: debug messages
	{
		$this->LogMsg($msg,$this->_logGroup,Zend_Log::DEBUG);
	}
	
	public function __construct($template_file,$config_file,$data)
	{
		// check if template file exists
		
		$template_file = realpath($template_file);
		$config_file   = realpath($config_file);

		if (file_exists($template_file))
			$this->template = $template_file;
		else
			$this->Log("Template $template_file bestaat niet.");
		
		if (file_exists($config_file))
			$this->config    = new Zend_Config_Ini($config_file,APPLICATION_ENV);
		else
			$this->Log("Config $config_file bestaat niet.");
		
		$this->data	 = $data;		
	}
	
	private function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		 $drawingString = @iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
		 $characters = array();
		 for ($i = 0; $i < strlen($drawingString); $i++) {
			 $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
		 }
		 $glyphs = $font->glyphNumbersForCharacters($characters);
		 $widths = $font->widthsForGlyphs($glyphs);
		 $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
		 return $stringWidth;
	}
	
	private function drawText($text,$x,$y,$font,$fontsize,$align=Zend_Pdf_Page::ALIGN_LEFT)
	{
		$height 	= $this->pdf->pages[$this->pagenum]->getHeight();
		$ml         = $this->config->page->margin_left;
		$pl			= $this->config->page->padding_left;
		$mt			= $this->config->page->margin_top;
		$pt			= $this->config->page->padding_top;
		
		if ($align == Zend_Pdf_Page::ALIGN_LEFT)
			$x = $x;
		else
			$x = $x-$this->widthForStringUsingFontSize($text,$font,$fontsize);
		
		$this->pdf->pages[$this->pagenum]->drawText($text,$ml+$pl+$x,$height-$mt-$pt-$y);
		
	}
	
	private function drawDataLine($line,$x,$y,$font,$fontsize,$align=Zend_Pdf_Page::ALIGN_LEFT){
		
		$text = "";
		foreach($line->keys as $key)
			$text .= $this->data[$key]." ";
		
		if (isset($line->label))
				$text = $line->label.$text;
				
		$this->drawText($text,$x,$y,$font,$fontsize,$align);
	}
	
	private function drawLines($font,$fontSize){
		
		$this->pdf->pages[$this->pagenum]->setFont($font, $fontSize);
		
		foreach($this->config->lines as $line){
			
			$x 			= $line->x;
			$y 			= $line->y;
			$lineheight = $line->lineheight;
			$align		= (isset($line->textalign) ? $line->textalign : Zend_Pdf_Page::ALIGN_LEFT);
			
			foreach($line->line as $dataline){				
				$this->drawDataLine($dataline,$x,$y,$font,$fontSize,$align);
				$y += $lineheight;
			}
		}
	}
	
	private function drawCell($column,$data,$y,$font,$fontsize){		
		
		$text = $data[$column->key];
		if (isset($column->label))
				$text = $column->label.$text;
				
		$align		= (isset($column->textalign) ? $column->textalign : Zend_Pdf_Page::ALIGN_LEFT);
		
		$this->drawText($text,$column->x,$y,$font,$fontsize,$align);		
	}
	
	private function drawTables($font,$fontSize){
		
		if (isset($this->config->tables)){		
			$this->pdf->pages[$this->pagenum]->setFont($font, $fontSize);
			
			foreach($this->config->tables as $table){
				
				$y 			= $table->y;
				$lineheight = $table->lineheight;
				
				foreach($this->data[$table->tablekey] as $datarow){
					
					foreach($table->column as $column){				
						$this->drawCell($column,$datarow,$y,$font,$fontSize);
						
					}
					
					$y += $lineheight;
				}
			}
		}
	}
	
	private function drawTextBlocks(){
		
		if (isset($this->config->textblocks)){
			foreach($this->config->textblocks as $textblock){	
				
				$text 	= $this->data[$textblock->key];
				$height = $this->pdf->pages[$this->pagenum]->getHeight();
				
				$this->pdf->pages[$this->pagenum]->drawTextBlock($text, $textblock->x, $height-($textblock->y), $textblock->width, $textblock->height, Zend_Pdf_Page::ALIGN_JUSTIFY);				
				
			}			
		}
	}
	
	private function drawBarcode(){
		
		if (isset($this->config->barcode)){		
			
			$barcode_string = $this->data[$this->config->barcode->key];
			
			$barcodeOptions  = array('text' => $barcode_string);
			$rendererOptions = array();
			$imageResource   = Zend_Barcode::factory('code39', 'image', $barcodeOptions, $rendererOptions)->draw();
			
			
			$height 	= $this->pdf->pages[$this->pagenum]->getHeight();
			$imagew 	= imagesx($imageResource);  
			$imageh 	= imagesy($imageResource);
			
			$tempfilename = tempnam(realpath($_ENV['TMPDIR']),"bar");
			rename($tempfilename,$tempfilename.".jpg");
			$tempfilename = $tempfilename.".jpg";		
			
			$this->Log("tempfilename = ".$tempfilename);
						
			imagejpeg($imageResource,$tempfilename);
			$image = Zend_Pdf_Image::imageWithPath($tempfilename);
			
			$x1 = $this->config->barcode->x;
			$y1 = $height-$this->config->barcode->y-$imageh;
			$x2 = $x1+$imagew;
			$y2 = $y1+$imageh;
			
			$this->pdf->pages[$this->pagenum]->drawImage($image,$x1,$y1,$x2,$y2);														 
			$this->Log("draw image ($x1,$y1,$x2,$y2)");
			
			unlink($tempfilename);			
		}
	}
	
	private function isWriteable($filename){
		
		if (($file = @fopen($filename, 'wb')) === false )
			return false;
		else
			return true;
		
	}
	
	public function renderPdf($output_filename=false)
	{
		if (($this->template !== NULL) && ($this->config !== NULL))
		{
			$this->pdf 		= Zend_Pdf::Load($this->template);
			$this->pagenum 	= 0;
			
			$font 			= Zend_Pdf_Font::fontWithName($this->config->font->family);
			$width  		= $this->pdf->pages[0]->getWidth();
			$height 		= $this->pdf->pages[0]->getHeight();
			
			$this->drawLines($font,$this->config->font->size);
			$this->drawTables($font,$this->config->font->size);
			$this->drawBarcode();
			$this->drawTextBlocks();
			
			if ((!$this->isWriteable($output_filename)) && ($output_filename)){
				$this->Log("output file ($output_filename) is niet writable.");
				return false;
			}
			else
				$this->Log("output file : ($output_filename) gemaakt");
				
			if ($output_filename)
				$this->pdf->save($output_filename);
							
			return $this->pdf->render();
		}	
		else
			return NULL;
		
	}
}