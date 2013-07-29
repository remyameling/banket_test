//
// Select box filters. <select id="level1" filter="level2;other_element;yet_another_element">
//	                          <option value="group1">group 1</option>
//	                          <option value="group2">group 2</option>
//                     </select>
//                     <select id="level2">
//                            <option  value="first#group1">first</option>
//							  <option value="second#group1">second</option>
//							  <option value="firstb#group2">first</option>
//                            ..
//                      </select>

var orgElements = [];	// contains copies of all select elements with their orginal values
var orgSelected = [];

function SaveSelectValues(selectID){
	// save a copy of the select into orgElements
	values = [];
	$("#"+selectID).find('option').each(function(){
		values[''+$(this).val()] = $(this).text();	
    });
	orgElements[selectID] = values;	
	orgSelected[selectID] = $("#"+selectID).val();
}

function RestoreSelectValues(selectID){
	// restore select element's original values
	$("#"+selectID).find("option").remove();  // remove current elements
	
	var selectValues = orgElements[selectID];
	
	for (var i in selectValues){			 // repopulate select
		$("#"+selectID).append($("<option></option>").attr("value",i).text(selectValues[i]));		
	}
	$("#"+selectID).val(orgSelected[selectID]);
}

function GetFilterElements(separated_elements_str){
	// splits all ;-seperated elements
	// into an array
	var elements = [];
	var start 	 = separated_elements_str.indexOf(";");
	var i        = 0;
	
	if (start>0){
		while(start > 0)
		{
			elements[i] 			= separated_elements_str.substr(0,start);
			separated_elements_str	= separated_elements_str.substr(start+1);
			start                   = separated_elements_str.indexOf(";");
			i++
		}
	}
	
	elements[i] = separated_elements_str;
	return elements;
}

function SetFilter(SelectIds,filterValue,bSetDefault){	
	// filter on all options with value (.)*#filterValue
	// by setting or removing class "hidden"
	
	//console.log("SetFilter("+SelectIds+","+filterValue+","+bSetDefault+")");
	
	var elements = GetFilterElements(SelectIds);
	
	for(i=0;i<elements.length;i++)
	{
		var SelectId = elements[i];
		var first = null;
		
		// restore copy 
		
		RestoreSelectValues(SelectId);		
		
		$("#"+SelectId).find('option').each(function() {
			var start = $(this).val().indexOf("#");
			var test  = $(this).val().substr(start+1);
			
			if (start >= 0) // # gevonden
			{
				if (test == filterValue)
				{
					$(this).removeClass("hidden");
					if (first == null)
						first = $(this).val();
				}
				else
					$(this).addClass("hidden");	
			}
			else
				first = $(this).val();				
		});
		
		if (bSetDefault)
			$("#"+SelectId).val(first);
			
		// remove all elements with class = "hidden"
		$("#"+SelectId).find("option.hidden").remove();		
	}	
}

function initFilters(){
	// add an onChange event to all select elements that
	// have an attribute with name 'filter' (value = filter element id)
	
	
	
	$("select[filter]").each(function() {
		
		$(this).change(function(){		
			var val 			    = $(this).val();
			var filter_element_ids  = $(this).attr('filter');
			
			var	element_ids		   = filter_element_ids.split(";");
			
			for(var id in element_ids)
			{
				SetFilter(element_ids[id],val,true);
			}
			
			
	   	});
	});
	
	// set the filter initially
	$("select[filter]").each(function(){
		
		var filter_id = $(this).attr('filter');
		var elements  = GetFilterElements(filter_id);
		
		var i=0;
		for(i=0;i<elements.length;i++)
		{
			element_id = elements[i];			
			var val    = $(this).val();
				
			SaveSelectValues(element_id);				
			SetFilter(element_id,val,false);
		}
    });
}


$(document).ready(function(){ initFilters() });

