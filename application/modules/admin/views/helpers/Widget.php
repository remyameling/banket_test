<?php 

class Admin_View_Helper_Widget
{
   private $_model = NULL; 
	
	
   private function getTableHtml($widget_data){
   		
   		$html  = '<h2>'.$widget_data['title'].'</h2>';
   		$html .= '<table class="index" cellspacing="0">';
   		
   		$title = $widget_data['rows'][0];
   		unset($widget_data['rows'][0]);
   		
   		// header
   		$html .= '<thead><tr>';
   			foreach($title['cols'] as $col){
   				$html.= '<th>'.$col.'</th>';
   			}
   		$html .= '</tr></thead>';
   		
   		// body
   		$html .= '<tbody>';
   		foreach($widget_data['rows'] as $row){
   			$html .= '<tr>';
   			foreach($row['cols'] as $col){
   				
   				if ($col == "")
   					$col = '&nbsp;';
   				
   				$html.= '<td>'.$col.'</td>';
   			}
   			$html .= '</tr>';   			
   		}
   		$html .= '</tbody>';
   		$html .= '</table>';
   		
   		return $html;
   	
   }
   
   public function Widget($widget_data)
   {
   		if ($widget_data['type'] == 'table')
   			return $this->getTableHtml($widget_data);
   }
}