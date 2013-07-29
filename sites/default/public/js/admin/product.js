// JavaScript Document

$(document).ready(function(){
	$("#product_price-element").html($("#product_price-element").html()+'<input type="checkbox" id="product_price_netto" name="product_price_netto"  />');
	$("#product_discount_price-element").html($("#product_discount_price-element").html()+'<input type="checkbox" id="product_discount_price_netto" name="product_discount_price_netto"  />');
	
	$("#product_price_netto").click(function(){
		if ($(this).is(':checked'))
			$("#product_price-label label").html("Netto prijs:");
		else
			$("#product_price-label label").html("Bruto prijs:");
								 
	});
	
	$("#product_discount_price_netto").click(function(){
		if ($(this).is(':checked'))
			$("#product_discount_price-label label").html("Netto aktie prijs:");
		else
			$("#product_discount_price-label label").html("Bruto aktie prijs:");
								 
	});
});