<?php

class App_PdfCreator
{
	protected $_logGroup 	= "ADMIN";
	
	private   $template = NULL;
	private   $data     = NULL;
	private   $config   = NULL; 
	private   $pagenum  = 0;
	
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
	
	public function __construct($template_file)
	{
		assert($template_file !== NULL);
		
		
		// check if template file exists
		
		$template_file = realpath($template_file);
		
		if (file_exists($template_file))
			$this->template = $template_file;
		else
			$this->Log("Template $template_file bestaat niet.");
			
		$this->pdf 		= Zend_Pdf::Load($this->template);
		$this->pagenum 	= 0;			
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
	
	private function drawText($text,$x,$y,$font,$fontsize,$align='left')
	{
		$height 	= $this->pdf->pages[$this->pagenum]->getHeight();
		$ml         = $this->config->page->margin_left;
		$pl			= $this->config->page->padding_left;
		$mt			= $this->config->page->margin_top;
		$pt			= $this->config->page->padding_top;
		
		if ($align == 'left')//Zend_Pdf_Page::ALIGN_LEFT)
			$x = $x;
		else
			$x = $x-$this->widthForStringUsingFontSize($text,$font,$fontsize);
		
		$this->pdf->pages[$this->pagenum]->drawText($text,$ml+$pl+$x,$height-$mt-$pt-$y,'UTF-8');
		
	}
	
	private function txtReplace($text,$replacement)
	{
		if (!is_array($text)){
			foreach($replacement as $string => $replacement_string)
			
				if (!is_array($replacement_string))			
					$text = str_replace("%".$string."%",$replacement_string,$text);
						
			return $text;
		}
	}
	
	private function drawDataLine($line,$x,$y,$font,$fontsize,$align='left'){
		
		$text = "";
		
		//print_r($line);
		//die();
		
		if (isset($line->keys)){
			foreach($line->keys as $key){
				
				if (isset($this->data[$key]))
					$text .= $this->data[$key]." ";
				else
					$text .= "no data ";
			}
		}
		
		if (isset($line->label))
				$text = $this->txtReplace($line->label,$this->data).$text;
				
		$this->drawText($text,$x,$y,$font,$fontsize,$align);
	}
	
	private function drawTextLines($defFont,$defFontSize){
		
		foreach($this->config->lines as $line){
			
				
			
				$bDrawLine = true;
			
				if ((isset($line->skiponempty)) && (empty($this->data[$line->skiponempty])))
					$bDrawLine = false;
				
				if ($bDrawLine){
				
					if (isset($line->font))
						$font		= Zend_Pdf_Font::fontWithName($line->font);
					else
						$font		= $defFont;
						
					if (isset($line->fontsize))
						$fontSize   = $line->fontsize;
					else
						$fontSize   = $defFontSize;
					
					$this->pdf->pages[$this->pagenum]->setFont($font, $fontSize);
					
					$x 			= (isset($line->x) ? $line->x : 0);
					$y 			= (isset($line->y) ? $line->y : 0);
					$lineheight = (isset($line->lineheight) ? $line->lineheight : floor($fontSize*1.5));
					
					$align		= (isset($line->textalign) ? $line->textalign : 'left');//Zend_Pdf_Page::ALIGN_LEFT);
					
					if ($line->line !== NULL){
						foreach($line->line as $dataline){				
							$this->drawDataLine($dataline,$x,$y,$font,$fontSize,$align);
							$y += $lineheight;
						}
					}
				}
			
		}
	
	}
	
	private function drawCell($column,$data,$y,$font,$fontsize){		
		
		if (isset($data[$column->key])){
		
			$text = $data[$column->key];
			if (isset($column->label))
					$text = $column->label.$text;
					
			$align		= (isset($column->textalign) ? $column->textalign : Zend_Pdf_Page::ALIGN_LEFT);
			
			$this->drawText($text,$column->x,$y,$font,$fontsize,$align);
		}	
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
	
	private function drawLines(){
		
		if (isset($this->config->drawlines)){
			foreach($this->config->drawlines as $line){	
				
				
				
				$x1 = $line->from->x;
				$y1 = $line->from->y;
				$x2 = $line->to->x;
				$y2 = $line->to->y;
				
				$height = $this->pdf->pages[$this->pagenum]->getHeight();
				
				$this->pdf->pages[$this->pagenum]->drawLine($x1, $height-$y1, $x2, $height-$y2);				
				
			}			
		}
	}
	
	private function drawBarcode(){
		
		if (isset($this->config->barcode)){		
			
			$barcode_string  = $this->data[$this->config->barcode->key];
			$code            = $this->config->barcode->get('code');
			if ($code === NULL)
				$code        = 'code39';
			
			$barcodeOptions  = array('text' => $barcode_string);
			$rendererOptions = array('imageType'=>'png','height'=>0,'width'=>600);
			$imageResource   = Zend_Barcode::factory($code, 'image', $barcodeOptions, $rendererOptions)->draw();
			
			
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
	
	public function addPage($config_file,$data,$config_section='all_pages')
	{
		if (file_exists($config_file))
			$this->config    = new Zend_Config_Ini($config_file,$config_section);
		else
			$this->Log("Config $config_file bestaat niet.");
		
		$this->data	 = $data;	

		$template 							= $this->pdf->pages[0];
		$new_page							= new Zend_Pdf_Page($template);
		$this->pagenum++;
		$this->pdf->pages[$this->pagenum] 	= $new_page;
		
		$font 			= Zend_Pdf_Font::fontWithName($this->config->font->family);
		$width  		= $this->pdf->pages[$this->pagenum]->getWidth();
		$height 		= $this->pdf->pages[$this->pagenum]->getHeight();
		
		$this->drawTextLines($font,$this->config->font->size);
		$this->drawTables($font,$this->config->font->size);
		$this->drawBarcode();
		$this->drawTextBlocks();
		$this->drawLines();

		
	}
	
	public function renderPdf($output_filename=false)
	{
		// verwijder template page (0)
		unset($this->pdf->pages[0]);
		
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
}