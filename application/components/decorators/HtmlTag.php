<?php

class My_Decorator_HtmlTag extends Zend_Form_Decorator_HtmlTag
{
    protected function _getOpenTag($tag, array $attribs = null)
    {
		$element 	 = $this->getElement();		
		$elementName = $this->getElement()->getName();	
		$class 		 = $this->getElement()->getAttrib('class');	
		
		if ($class != ""){
			
			$class   = $tag."-".$element->getAttrib('class');
			$class = ' class="'.$class.'"';
		}
		else{
			
			$class = "";
		}
	
        $html = '<' . $tag . $class;
        if (null !== $attribs) {
            $html .= $this->_htmlAttribs($attribs);
        }
        $html .= '>';
        return $html;
    }
}
