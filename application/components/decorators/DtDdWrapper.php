<?php

class My_Decorator_DtDdWrapper extends Zend_Form_Decorator_DtDdWrapper
{
    public function render($content)
    {
        $elementName = $this->getElement()->getName();
		$className   = $this->getElement()->getAttrib('class');
		$label	     = $this->getElement()->getAttrib('label');
		$dtclass     = "";
		$ddclass     = "";
		
		if ($className != ""){
			$dtclass='class="dt-'.$className.'"';
			$ddclass='class="dd-'.$className.'"';
		}
		
		if ($label == "")
			$label = "&nbsp;";
		else
			$label = "<label>".$label."</label>";
		
        return '<dt '.$dtclass.' id="' . $elementName . '-label">'.$label.'</dt>' .
               '<dd '.$ddclass.' id="' . $elementName . '-element">' . $content . '</dd>';
    }
}
