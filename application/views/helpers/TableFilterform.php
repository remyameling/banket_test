<?php

class App_View_Helper_TableFilterform extends Zend_View_Helper_Abstract
{
	
	private function renderSelectElement($name,$filter,$helper)
	{
		$output  = "";	
		$output .= "<label>".$filter['label']."</label>".PHP_EOL;
		$output .= '<select name="'.$name.'" onchange="this.form.submit();">'.PHP_EOL;
		
		$selected = $helper->getFilterValue($name);
		$values   = $filter['val'];
		
		foreach($values as $value=>$value_str){
			
			if ($value == $selected)
				$selectedstr = 'selected="selected"';
			else
				$selectedstr = '';
			
			$output .= '<option value="'.$value.'" '.$selectedstr.'>'.$value_str.'</option>'.PHP_EOL;	
		}
		
		$output .= '</select>'.PHP_EOL;
		return $output;
	}
	
	private function renderDateElement($name,$filter,$helper,$bAutoSubmit)
	{
		$output  = "";

		if ($bAutoSubmit)
			$onchange = 'onchange="this.form.submit();"';
		else
			$onchange = '';
		
		$output .= "<label>".$filter['label']."</label>".PHP_EOL;
				
		$default = $helper->getFilterValue($name);
		
		$output .= '<input type="text" id="'.$name.'" name="'.$name.'" class="datepicker" '.$onchange.' value="'.$default.'" />'.PHP_EOL;
		
		return $output;
	}
	
	public function TableFilterform($helper,$formid,$url=NULL,$bAutoSubmit=true,$bSubmitLabel="Verzenden")
   	{
		$filters = $helper->getFilters();
		
		if ($url === NULL)
			$url = $this->view->url(array('order'=>$helper->getOrder(),'sort'=>$helper->getSort(),'page'=>1));
			
		
		$output = '<form my="my" id="'.$formid.'" method="post" action="'.$url.'" >'.PHP_EOL;
		foreach($filters as $name=>$filter){
			switch($filter['type']){
				case App_TableHelper::FILTER_SELECT:
					$output .= $this->renderSelectElement($name,$filter,$helper);
					break;
				case App_TableHelper::FILTER_DATE:
					$output .= $this->renderDateElement($name,$filter,$helper,$bAutoSubmit);
					break;
				default:
					break;
			}			
		}
		if (!$bAutoSubmit){
			$output .= '<input type="submit" value="'.$bSubmitLabel.'"/>';
		}
		$output .= "</form>".PHP_EOL;
		
		return $output;

   	}
}