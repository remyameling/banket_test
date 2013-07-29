<?php 

class Admin_View_Helper_AlphaPaginator
{
   public function AlphaPaginator($pages)
   {
   		if ($pages !== NULL)
   		{
   			foreach($pages as $text=>$href)
   				echo '<a href="'.$href.'">'.$text.'</a>&nbsp;|&nbsp;';
   		}
   		else
   			echo "";
   }
}