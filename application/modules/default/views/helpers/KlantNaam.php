<?php

class Default_View_Helper_KlantNaam extends Zend_View_Helper_Abstract
{
	public function KlantNaam($klant_data)
   	{
   		assert($klant_data !== NULL);
   		
   		$npnaam = $klant_data['klant_achternaam'].", ".$klant_data['klant_voornaam'];
   		
   		if (isset($klant_data['klant_bedrijfsnaam']))
   		{
   			$ret  = '<span class="nnp">';
   			$ret .= $klant_data['klant_bedrijfsnaam'];
   			$ret .= '</span>';
   			$ret .= '<span class="np">';
   			$ret .= " t.a.v. ".$npnaam;
   			$ret .= '</span>';
   		}
   		else
   			$ret  = '<span class="np">'.$npnaam.'</span>';
   			
   		return $ret;
   		
   	}
}