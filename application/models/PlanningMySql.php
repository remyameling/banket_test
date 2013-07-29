<?php 


class App_Model_PlanningMySql extends App_Model_Planning
{
	
	function fetchArtikelNr($lijn,$ordernr,$orderregel)
	{
		return array('ARTIKELNR'=>541111451);
	}
	
	public function fetchPlannedOrders($lijn,$date,$site_name)
	{
		// STUB for testing
		
		$this->_Init($site_name);
		
		$order_nr 		= $this->_getOrderNumber($date,$lijn);
		$num_dozen		= array(10,610,711,400,181,40,101,330,150,100,60,50,150,10,60,70,40,180,40,10,330,150,100,60,50,150);
		$ordernr 		= $this->_getOrderNumber($date,$lijn);

		for($i=1;$i<=10;$i++)
			$orders[] = array('PDALNR'=>'541111440',
							  'PDOMAL'=>'Valerie 15x300g Verse ei wafels     2748',
							  'PDORNR'=>$order_nr,
							  'PDORRL'=>$i*100,
							  'NUM_DOZEN'=>$num_dozen[$i],
							  'MAX_TIME'=>'211457',
							  'NUM_DOZEN_PER_PALLET'=>54,
							  'NUM_PALLETS_GEREEDGEMELD'=>11);
			
		return $orders;
	}
	
	public function fetchPlannedOrder($ordernr,$orderrl,$site_name)
	{
		// STUB for testing
		
		$this->_Init($site_name);
		
		return array('PDORNR'=>$ordernr,
					 'PDORRL'=>$orderrl,
					 'PDOMAL'=>'Artikel omschrijving',
					 'PDALNR'=>'541111440',
					 'NUM_DOZEN'=>620,
					 'NUM_DOZEN_PER_PALLET'=>54,
					 'MAX_TIME'=>'211457',
					 'NUM_PALLETS_GEREEDGEMELD'=>11);			
	}
	
	public function fetchLatestReady($ordernr,$orderrl,$site_name)
	{
		// STUB for testing
		
		$this->_Init($site_name);
		
		return array('MAX_TIME'=>'211457',
					 'NUM_PALLETS_GEREEDGEMELD'=>11);			
	}
	
	public function fetchCurrentOrder($lijn,$date,$site_name)
	{
		// STUB for testing
		
		$this->_Init($site_name);
		
		$order_nr = $this->_getOrderNumber($date,$lijn);
		
		return array('VTORNR'=>$order_nr,
					 'VTORRL'=>200);
	}
}

?>