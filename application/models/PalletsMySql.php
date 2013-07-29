<?php 

class App_Model_PalletsMySql extends App_Model_Pallets
{
	public function fetchMagazijnpallets($date,$site_name,$order,$sort)
	{
		
		$ordernr	= $this->_getOrderNumber($date,"XX");
		
		return array(
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '511101203' , 'AROMT1' => 'Miniwafel 24x250 gr. EVGA'               ,   'NUM_PALLETS' => '15',  'NUM_DOZEN' =>   '600.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '521116320' , 'AROMT1' => 'Toastwafel 24x250g Firenze '             ,  'NUM_PALLETS' => '6',   'NUM_DOZEN' => 	 '288.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '511121008' , 'AROMT1' => 'Miniwf.24x250g 15031Kaufland EXPORT RSPO',  'NUM_PALLETS' => '6',    'NUM_DOZEN' =>   '288.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '511109504' , 'AROMT1' => 'Miniwafel 12x250g.Pralinee OU '          ,  'NUM_PALLETS' => '42',    'NUM_DOZEN' =>  '3360.000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '523117440' , 'AROMT1' => 'Sneeuwwafel 24x250g Globus'              ,  'NUM_PALLETS' => '30',    'NUM_DOZEN' =>  '1440.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '523114001' , 'AROMT1' => 'Suikertoastw. 12x250g. Carrefour'        ,  'NUM_PALLETS' => '161',    'NUM_DOZEN' => '12880.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '523114801' , 'AROMT1' => 'Gaufres sucre.12x250g,Cactus'            ,  'NUM_PALLETS' => '33',    'NUM_DOZEN' =>  '2640.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '523123560' , 'AROMT1' => 'Sneeuwwafel 12x250g Monoprix'            ,  'NUM_PALLETS' => '89',    'NUM_DOZEN' =>  '7120.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '551116680' , 'AROMT1' => 'Miniwaf.12x165g, BIO Fresh Food'         ,  'NUM_PALLETS' => '26',    'NUM_DOZEN' =>  '3120.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '523121102' , 'AROMT1' => 'Gaufr.poudre12x250gSysteme U'            ,  'NUM_PALLETS' => '249',    'NUM_DOZEN' => '19920.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '521109504' , 'AROMT1' => 'Toastwafel La Mole 24 x 250 g.'          ,  'NUM_PALLETS' => '21',    'NUM_DOZEN' =>  '1008.0000', 'QLMAGN'=>'X.B'),
		array('VTORNR'=>'P1307506','ONTSTAAN' => '2013-2-15','VTALNR' => '521119301' , 'AROMT1' => '#FlashToastwafel,24x250,Carlton'         ,  'NUM_PALLETS' => '7',    'NUM_DOZEN' =>   '336.0000', 'QLMAGN'=>'X.B'));
		
	}
	
	public function fetchByOrderAndCode($site_name,$artikelnr,$ordernr,$magazijncode,$order,$sort)
	{
		return array(
			array(
				'VTALNR' 	=> '511112311', 
				'AROMT1' 	=> 'Miniwafel,12x250gr Emte', 
				'VTORNR' 	=> 'P1307506',
				'VTORRL' 	=> '200',
				'VTTIME' 	=> '85501', 
				'VTQANT' 	=> '80.0000', 
				'VTSSCC' 	=> '087105450050873454', 
				'QLMAGN' 	=> 'V.E',
				'ONTSTAAN' 	=> '2013-2-15'),
			array(
				'VTALNR' 	=> '511112311', 
				'AROMT1' 	=> 'Miniwafel,12x250gr Emte', 
				'VTORNR' 	=> 'P1307506',
				'VTORRL' 	=> '200', 
				'VTTIME' 	=> '92041', 
				'VTQANT' 	=> '80.0000', 
				'VTSSCC' 	=> '087105450050873515', 
				'QLMAGN' 	=> 'V.E', 
				'ONTSTAAN' 	=> '2013-2-15'));
	}
	
	public function fetchCountByOrdernum($site_name,$date,$order='ORDERNR',$sort='ASC')
	{
		return array(array('ORDERNR' => 'P1310501100','NUM_DOZEN'=>'486.0000'),
					   array('ORDERNR' => 'P1310501200','NUM_DOZEN'=> '40.0000'),
					   array('ORDERNR' => 'P1310502100','NUM_DOZEN'=>'514.0000'),
					   array('ORDERNR' => 'P1310502200','NUM_DOZEN'=>'120.0000'),
					   array('ORDERNR' => 'P1310502300','NUM_DOZEN'=> '175.0000'));
	}
	
	public function fetchGroupedByArtikel($date,$site_name,$order,$sort){

		
		return array ( array ( 'VTALNR' => '511113702','NUM_PALLETS' => '17','AROMT1' => 'Frischeiwaf.24x250 Aldi Nord RSPO/MB','VTTIME'=>'224932','THT'=>'31-5-2013','VTQANT'=>'816.0000','GEWICHT'=>'4896.00000000'),
        array ( 'VTALNR' => '511113708','NUM_PALLETS' => '35','AROMT1' => 'Softwafel 24x250 AldiUK','VTTIME'=>'220411','THT'=>'31-5-2013','VTQANT'=>'1584.0000','GEWICHT'=>'9504.00000000'),
        array ( 'VTALNR' => '511114706','NUM_PALLETS' => '62','AROMT1' => 'Frischeiw12x250g.G&G.40er 15028 RSPO/MB','VTTIME'=>'210311','THT'=>'31-5-2013','VTQANT'=>'2480.0000','GEWICHT'=>'7440.00000000'),
        array ( 'VTALNR' => '511114707','NUM_PALLETS' => '11','AROMT1' => 'Aldente,24x250g.,15029 Bisco D RSPO/MB','VTTIME'=>'225132','THT'=>'31-5-2013','VTQANT'=>'528.0000','GEWICHT'=>'3168.00000000'),
        array ( 'VTALNR' => '511116680','NUM_PALLETS' => '6 ','AROMT1' => 'Miniwafel, 12x165g, Fresh Food','VTTIME'=>'92856','THT'=>'31-5-2013','VTQANT'=>'720.0000','GEWICHT'=>'1425.60000000'),
        array ( 'VTALNR' => '511117450','NUM_PALLETS' => '10','AROMT1' => 'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'224715','THT'=>'31-5-2013','VTQANT'=>'400.0000','GEWICHT'=>'1200.00000000'),
        array ( 'VTALNR' => '511126162','NUM_PALLETS' => '2 ','AROMT1' => 'Miniwafel 24x250g Penny CZ','VTTIME'=>'81224','THT'=>'31-5-2013','VTQANT'=>'96.0000','GEWICHT'=>'576.00000000'),
        array ( 'VTALNR' => '511128320','NUM_PALLETS' => '1 ','AROMT1' => 'Frischeiwaffel 12x250g Ritz','VTTIME'=>'95320','THT'=>'31-5-2013','VTQANT'=>'80.0000','GEWICHT'=>'240.00000000'),
        array ( 'VTALNR' => '521102301','NUM_PALLETS' => '15','AROMT1' => 'Toastwafel 16x325g Delhaize','VTTIME'=>'154901','THT'=>'31-5-2013','VTQANT'=>'720.0000','GEWICHT'=>'3744.00000000'),
        array ( 'VTALNR' => '521115401','NUM_PALLETS' => '6 ','AROMT1' => 'Toastwafel 16x330g Belsy','VTTIME'=>'181532','THT'=>'31-5-2013','VTQANT'=>'264.0000','GEWICHT'=>'1393.92000000'));
		
	}
	
	public function fetchByArtikel($date,$site_name,$vtalnr,$order,$sort){
		
		return Array ( Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'214015','VTSSCC'=>'087105450050945496','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'214016','VTSSCC'=>'087105450050945502','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'220509','VTSSCC'=>'087105450050945557','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'220511','VTSSCC'=>'087105450050945564','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'221845','VTSSCC'=>'087105450050945595','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'221848','VTSSCC'=>'087105450050945601','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'224708','VTSSCC'=>'087105450050945649','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'224710','VTSSCC'=>'087105450050945656','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'224713','VTSSCC'=>'087105450050945663','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'), 
				Array ( 'VTALNR' => '511117450','AROMT1'=>'Frischeiwfl2x250g.GlobusD.15025 RSPO','VTTIME'=>'224715','VTSSCC'=>'087105450050945670','THT'=>'31-5-2013','VTQANT'=>'40.0000','GEWICHT'=>'120.00000000'));
	}
	
	
}
		
?>