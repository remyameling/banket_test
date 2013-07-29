<?php 

abstract class App_Model_Planning extends App_Model_table
{
	protected function _Init($site_name)
	{
		
	}
	
	/*
	protected function _getOrderNumber($date,$lijn)
    {
    	$datet  = mktime(0,0,0,substr($date,3,2),substr($date,0,2),substr($date,6,4));
    	
    	$year   = substr($date,8,2);
		$week   = date("W",$datet);
		$day    = date("N",$datet);
		$line   = "0$lijn";
		
		//echo "year=$year; week=$week; day=$day; line=$line<br />";
		
		return "P$year$week$day$line";	
    }
    */
	
	abstract public function fetchPlannedOrders($lijn,$date,$site_name); 
	abstract public function fetchPlannedOrder($ordernr,$orderrl,$site_name);
	abstract public function fetchLatestReady($ordernr,$orderrl,$site_name);
	abstract public function fetchCurrentOrder($lijn,$date,$site_name);
   
}

?>