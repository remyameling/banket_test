<?php

class App_Cart
{
	protected $_logGroup 	= "CART";
	
	public function __construct()
    {
    	$this->LogMsg("cart geinitialiseerd",Zend_Log::DEBUG);
    	
    	if (!isset(Zend_Registry::getInstance()->session->cart->cart))
    	{
    		$this->reSet();
    	}
    }
    
	private function LogMsg($msg,$prio){
    	
    	if (Zend_Registry::getInstance()->logging->groups->get($this->_logGroup)){
    		
    		$function = "";
    		$class    = "";
    		
    		if (Zend_Registry::getInstance()->logging->logcaller){
    	
	    		$trace=debug_backtrace();
			
				$caller=array_shift($trace);
				$caller=array_shift($trace);
				$caller=array_shift($trace);
	
				$function = $caller['function'];
				if (isset($caller['class']))
					$class = $caller['class']."::";
    		}
				
			Zend_Registry::getInstance()->logger->log("CART:".$class.$function." ".$msg,$prio);
    	}    	
    }
    
	private function findItem($items,$item_name,$item_description,$item_price,$item_price_tax,$item_discount,$item_picture,$item_discount_str)
	{
		
		
		$found = -1;
		foreach($items as $index=>$item){
			
			if (($item['item_name'] 		== $item_name) &&
			    ($item['item_description'] 	== $item_description) &&
			    ($item['item_price'] 		== $item_price) &&
			    ($item['item_price_tax'] 	== $item_price_tax) &&
			    ($item['item_discount'] 	== $item_discount) &&
			    ($item['item_picture'] 		== $item_picture) &&
			    ($item['item_discount_str'] == $item_discount_str))
			{
				$found = $index;
			}			
		}
		
		$this->LogMsg("findItem() found=$found",Zend_Log::DEBUG);
		
		return $found;
	}
	
	private function makeItem($item_name,$item_product_number,$item_org_article_id,$item_description,$item_options,$item_price,$item_price_tax,$item_num_items,$item_discount,$item_picture,$item_discount_str,$item_shipping_category)
	{
		$item = array();
		
		$item['item_name'] 				= $item_name;
		$item['item_product_number'] 	= $item_product_number;
		$item['item_org_article_id']	= $item_org_article_id;
		$item['item_options']	    	= $item_options;
		$item['item_description'] 		= $item_description;
		$item['item_price'] 			= $item_price;
		$item['item_price_tax'] 		= $item_price_tax;
		$item['item_num_items'] 		= $item_num_items;
		$item['item_discount'] 			= $item_discount;
		$item['item_picture'] 			= $item_picture;
		$item['item_discount_str']  	= $item_discount_str;
		$item['item_shipping_category'] = $item_shipping_category;
		$item['item_total'] 			= $item['item_num_items']*($item['item_price']+$item['item_price_tax']-$item['item_discount']);
		$item['item_total_gross'] 		= $item['item_num_items']*($item['item_price']-$item['item_discount']);
		
		
		return $item;
	}
	
	public function reSet(){
		$this->LogMsg("reSet()",Zend_Log::DEBUG);
		
		Zend_Registry::getInstance()->session->cart->cart['items'] 	= array();
    	Zend_Registry::getInstance()->session->cart->cart['handling'] = array();
    	Zend_Registry::getInstance()->session->cart->cart['order'] = array();
    	Zend_Registry::getInstance()->session->cart->lastitem = NULL;
	}
	
	public function isEmpty()
	{
		$cart = Zend_Registry::getInstance()->session->cart->cart;
		assert($cart !== NULL);
		
		if ((!isset($cart['items'])) || (count($cart['items']) == 0))
		{
			$this->LogMsg("isEmpty(): true",Zend_Log::DEBUG);
			return true;
		}
		else
		{
			$this->LogMsg("isEmpty(): false",Zend_Log::DEBUG);
			return false;
		}
	}
	
	public function numItems()
	{
		$cart = Zend_Registry::getInstance()->session->cart->cart;
		return count($cart['items']);
	}
	
	private function nextId(){
		
		$nextId = 0;
		foreach(Zend_Registry::getInstance()->session->cart->cart['items'] as $key=>$item){			
			if ($key > $nextId)
				$nextId = $key;			
		}
		return $nextId+1;
	}
	
	
	public function addItem($item_name,$item_product_number,$item_org_article_id,$item_description,$item_options,
						    $item_price,$item_price_tax,
						    $item_num_items=1,$item_discount=0,
						    $item_picture=NULL,$item_discount_str="",$item_shipping_category=0)
	{
		
		$this->LogMsg("addItem($item_name,$item_product_number,$item_description,$item_price,$item_price_tax,$item_num_items,$item_discount,$item_picture,$item_discount_str)",Zend_Log::DEBUG);
		
		assert(strrpos($item_price,",") == 0);
		assert(strrpos($item_price_tax,",") == 0);
		assert(strrpos($item_discount,",") == 0);
		
		$cart  = Zend_Registry::getInstance()->session->cart->cart;
		assert($cart !== NULL);
		
		if (!$this->isEmpty())
			$found = $this->findItem($cart['items'],$item_name,$item_description,$item_price,$item_price_tax,$item_discount,$item_picture,$item_discount_str);
		else
			$found = -1;
			
		if ($found >= 0)
			Zend_Registry::getInstance()->session->cart->cart['items'][$found]['item_num_items'] = $cart['items'][$found]['item_num_items']+$item_num_items;
		else
		{
			$item = $this->makeItem($item_name,$item_product_number,$item_org_article_id,$item_description,$item_options,
								    $item_price,$item_price_tax,
						    		$item_num_items,$item_discount,$item_picture,$item_discount_str,$item_shipping_category);
						    		
			
						    		
			$next_id =  $this->nextId();
			
			$item['id']     = $next_id;					    		
			Zend_Registry::getInstance()->session->cart->cart['items'][$next_id] = $item;
			
		}
		Zend_Registry::getInstance()->session->cart->lastitem = $item_org_article_id;
			
	}
	
	public function lastItem(){
		return Zend_Registry::getInstance()->session->cart->lastitem;
	}
	
	public function updateItem($item_index,$item_num_items,$item_price=NULL,$item_tax=NULL,$product_name=NULL)
	{
		if ($item_num_items == 0)
			$this->deleteItem($item_index);
		else if ($item_num_items > 0)
		{
			Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_num_items'] = $item_num_items;
			if ($item_price !== NULL)
				Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_price'] = $item_price;
			if ($item_tax !== NULL)
				Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_price_tax'] = $item_tax;
				
			$item_tax 		= Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_price_tax'];
			$item_discount  = Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_discount'];
				
			Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_total'] = 
				$item_num_items*($item_price+$item_tax-$item_discount);
			Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_total_gross'] = 
				$item_num_items*($item_price-$item_discount);
				
				
			if ($product_name !== NULL)
				Zend_Registry::getInstance()->session->cart->cart['items'][$item_index]['item_name'] = $product_name;
			
			
		}
	}
	
	public function deleteItem($id)
	{
		$cart  = Zend_Registry::getInstance()->session->cart->cart;
		assert($cart !== NULL);
				
		unset(Zend_Registry::getInstance()->session->cart->cart['items'][$id]);			
	}
	
	public function fetchItem($index)
	{
		$cart  = Zend_Registry::getInstance()->session->cart->cart;
		assert($cart !== NULL);
		assert(isset($cart['items'][$index]));
		
		return $cart['items'][$index];			
	}
	
	public function fetchItems()
	{
		$cart  = Zend_Registry::getInstance()->session->cart->cart;
		assert($cart !== NULL);
		
		return $cart['items'];			
	}
	
	public function getContent()
	{
		return Zend_Registry::getInstance()->session->cart->cart['items'];
	}
	
	public function setOrderText($texta,$textb,$textc)
	{
		$this->LogMsg("setOrderText($texta,$textb,$textc)",Zend_Log::DEBUG);
		
		assert(Zend_Registry::getInstance()->session->cart->cart['order'] !== NULL);
		Zend_Registry::getInstance()->session->cart->cart['order']['order_texta'] = $texta;
		Zend_Registry::getInstance()->session->cart->cart['order']['order_textb'] = $textb;
		Zend_Registry::getInstance()->session->cart->cart['order']['order_textc'] = $textc;
	}
	
	public function getOrderTexta()
	{
		$this->LogMsg("getOrderTexta()",Zend_Log::DEBUG);
		
		return Zend_Registry::getInstance()->session->cart->cart['order']['order_texta'];
	}
	
	public function getOrderTextb()
	{
		$this->LogMsg("getOrderTextb()",Zend_Log::DEBUG);
		
		return Zend_Registry::getInstance()->session->cart->cart['order']['order_textb'];
	}
	
	public function getOrderTextc()
	{
		$this->LogMsg("getOrderTextc()",Zend_Log::DEBUG);
		
		return Zend_Registry::getInstance()->session->cart->cart['order']['order_textc'];
	}
	
	public function setOrderComments($comments)
	{
		$this->LogMsg("setOrderComments($comments)",Zend_Log::DEBUG);
		
		assert(Zend_Registry::getInstance()->session->cart->cart['order'] !== NULL);
		Zend_Registry::getInstance()->session->cart->cart['order']['comments'] = $comments;
	}
	
	public function setInvoiceAddress($address_id)
	{
		$this->LogMsg("setShippingAddress($address_id)",Zend_Log::DEBUG);
		
		assert(Zend_Registry::getInstance()->session->cart->cart['order'] !== NULL);
		Zend_Registry::getInstance()->session->cart->cart['order']['invoice_address_id'] = $address_id;
	}
	
	public function getInvoiceAddress()
	{
		$this->LogMsg("getInvoiceAddress()",Zend_Log::DEBUG);
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['order']['invoice_address_id']))
			return Zend_Registry::getInstance()->session->cart->cart['order']['invoice_address_id'];
		else
			return NULL;
	}
	
	public function getOrderComments()
	{
		$this->LogMsg("getOrderComments()",Zend_Log::DEBUG);
		
		return Zend_Registry::getInstance()->session->cart->cart['order']['comments'];
	}
	
	
	
	public function setShipping($shipping_method)
	{
		$this->LogMsg("setShipping($shipping_method)",Zend_Log::DEBUG);
		
		assert(Zend_Registry::getInstance()->session->cart->cart !== NULL);
		Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method'] = $shipping_method;
	}
	
	public function setPayment($payment_method)
	{
		$this->LogMsg("setPayment($payment_method)",Zend_Log::DEBUG);
		
		assert(Zend_Registry::getInstance()->session->cart->cart !== NULL);
		Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method'] = $payment_method;
	}
	
	public function getShippingMethod()
	{
		return Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method'];
	}
	
	public function getShippingStr()
	{
		$shipMdl 	= new App_Model_Shipping();
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method']))
		{
			$data = $shipMdl->fetchEntry(Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method']);
			return $data['shipping_str'];
		}
		else
			return "";
	}
	
	public function getShippingDescription()
	{
		$shipMdl 	= new App_Model_Shipping();
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method']))
		{
			$data = $shipMdl->fetchEntry(Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method']);
			return $data['shipping_description'];
		}
		else
			return "";
	}
	
	public function getShippingExp()
	{
		$shipMdl 	= new App_Model_Shipping();
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method']))
		{
			$data 		= $shipMdl->fetchEntry(Zend_Registry::getInstance()->session->cart->cart['handling']['shipping_method']);
			$category 	= $this->getShippingCategory();
			
			switch($category){
				case 1: $exp = $data['shipping_price_cata'];
						break;
				case 2: $exp = $data['shipping_price_catb'];
						break;
				case 3: $exp = $data['shipping_price_catc'];
						break;
			}
			
			return $exp;
		}
		else
			return 0;
	}
	
	public function getPaymentMethod()
	{
		return Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method'];
	}
	
	public function getPaymentStr()
	{
		$payMdl 	= new App_Model_Payment();
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method']))
		{
			$data = $payMdl->fetchEntry(Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method']);
			return $data['payment_str'];
		}
		else
			return "";
	}
	
	public function getPaymentDescription()
	{
		$payMdl 	= new App_Model_Payment();
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method']))
		{
			$data = $payMdl->fetchEntry(Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method']);
			return $data['payment_description'];
		}
		else
			return "";
	}
	
	public function getPaymentExp()
	{
		$payMdl 	= new App_Model_Payment();
		
		if (isset(Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method']))
		{
			$data = $payMdl->fetchEntry(Zend_Registry::getInstance()->session->cart->cart['handling']['payment_method']);
			return $data['payment_expense'];
		}
		else
			return 0;
	}
	
	public function getShippingCategory()
	{
		$cat   = 1;
		
		$items = $this->fetchItems();
		if (!empty($items)){
   			foreach($items as $item){
   				
   				if ($item['item_shipping_category'] > $cat)
   					$cat = $item['item_shipping_category'];
   			}		
   		}
   		
   		return $cat;
	}
	
	public function getCartTotalPrice($netprice=true,$bIncludeShipping=false,$bIncludePayment=false)
	{
		$price = 0;
   		
		$items = $this->fetchItems();
   		if (!empty($items)){
   			foreach($items as $item){
   			
   				$item_price = $item['item_price'];
   			
   				if ($netprice)
   					$item_price += $item_price * Zend_Registry::getInstance()->db_settings->tax;
   					
   				$item_price = round($item_price/100.00,2);   					
   				$item_price = $item_price*$item['item_num_items'];
   				$price     += $item_price;
   			}

   			$price = $price*100;
   		}
   		
   		
   		
   		if ($bIncludeShipping)
   		{
   			$expense 	= str_replace(",","",$this->getShippingExp());
   			$price 		= $price + (int) $expense;
   		}
   		
		if ($bIncludePayment)
   		{
   			$expense 	= str_replace(",","",$this->getPaymentExp());
   			$price 		= $price + (int) $expense;
   		}
   		
   		return $price;
	}
}