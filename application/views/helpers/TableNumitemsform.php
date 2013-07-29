<?php

class App_View_Helper_TableNumitemsform extends Zend_View_Helper_Abstract
{
	public function TableNumitemsform($helper,$formid,$label,$posttext="",$url=NULL)
   	{
		$filters = $helper->getFilters();
		
		if ($url === NULL)
			$url = $this->view->url(array('order'=>$helper->getOrder(),'sort'=>$helper->getSort(),'page'=>1));
			
		
		$output = '<form my="my" id="'.$formid.'" method="post" action="'.$url.'" >'.PHP_EOL;
		
		$output .= "<label>".$label."</label>".PHP_EOL;
		$output .= '<select name="ic" onchange="this.form.submit();">'.PHP_EOL;
			
		$selected = $helper->getItemcount();
		$values   = $helper->getItemcountValues();
			
		foreach($values as $value_str=>$value)
		{
			if ($value == $selected)
				$selectedstr = 'selected="selected"';
			else
				$selectedstr = '';
				
			$output .= '<option value="'.$value.'" '.$selectedstr.'>'.$value_str.'</option>'.PHP_EOL;	
		}
			
		$output .= '</select>';
		$output .= $posttext.PHP_EOL;
		$output .= "</form>".PHP_EOL;
		
		return $output;

   	}
}