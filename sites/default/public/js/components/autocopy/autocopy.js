//
// Autocopy: Voor alle input elementen met een attribuut "autocopy='id'" :
//
// indien het input element nog leeg is, wordt de inhoud van het element id
// hiernaartoe gekoieerd


$(document).ready(function(){
	
	// add an onFocus event to all elements that
	// have an attribute with name 'autocopy' (value = element id of element to copy)
	$("input[autocopy]").each(function() {
		$(this).focus(function(){		
			var val 	   = $(this).val();
			
			if (val == ""){
			
				var element_id = $(this).attr('autocopy');	
				var content    = $("#"+element_id).val();
				
				$(this).val(content);
			}
	   	});
	});
});

