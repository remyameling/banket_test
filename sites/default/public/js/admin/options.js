//
// Option filters      <input type="radio" name="name" id="id1" value="val1" checked="checked" showelements="element1;element2" />
//                     <input type="radio" name="name" id="id1" value="val2" showelements="element1;element2" />
//
//                     other elements
//					   
//					   <dt id="element1-label">label</dt>
//		               <dd id="element1-element">
//                     		<input id="element1" onoption="id1=val2" />
//					   </dd>
//                     <input id="element2" onoption="id1=val2" />

//
// shows element1,element2 etc, only if radio with val2 is selected
//


function GetFilterElements2(separated_elements_str){
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

/*

function SetFilter(SelectIds,filterValue,bSetDefault){	
	// filter on all options with value (.)*#filterValue
	// by setting or removing class "hidden"
	
	var elements = GetFilterElements(SelectIds);
	
	for(i=0;i<elements.length;i++)
	{
		var SelectId = elements[i];
		var first = null;
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
	}	
}
*/

function toggleElementContainers(id,on){
	
	if (on == 'show')
	{
		$("#"+id+"-label").removeClass("hidden");
		$("#"+id+"-element").removeClass("hidden");
	}
	else
	{
		$("#"+id+"-label").addClass("hidden");
		$("#"+id+"-element").addClass("hidden");
	}
}

$(document).ready(function(){
	// add an onChange event to all radio elements that
	// have an attribute with name 'showelements' (value = semicollon seperatod list of elements to show/hide)
	$("input[showelements],select[showelements]").each(function() {
										   
		$(this).change(function(){		
			var option_value	  = $(this).val();
			var element_id 		  = $(this).attr('showelements');			
			var elements          = GetFilterElements2(element_id);
			
			for(i=0;i<elements.length;i++)
			{
				// get onoption attribute of element
				var SelectId = elements[i];
				var onoption = $("#"+SelectId).attr('onoption');
				var start 	 = onoption.indexOf("=");
				var value    = onoption.substr(start+1);
				
				if (value == option_value)
					toggleElementContainers(elements[i],'show'); 
				else
					toggleElementContainers(elements[i],'hide');			
				
			}								
	   	});
	});
	
	
	// show/hide the elements initially
	$("[onoption]").each(function(){
								  
		var attr      		= $(this).attr('onoption');
		var element_name   	= attr.substr(0,attr.indexOf("="));
		var value     		= attr.substr(attr.indexOf("=")+1);
		
		if ($("#"+element_name).length > 0)  // indien het select element bestaat, dan is het een select
		{	
			var v 				= $("#"+element_name).val();
			var checked         = false;
			if (v == value)
				checked = true;
		}
		else								// zo niet, dan (aanname) een radio
		{
			var elementid 		= element_name+"-"+value;
			var checked   		= $("#"+elementid).attr('checked');
		}
		
		if (!checked)
			toggleElementContainers($(this).attr('id'),'hide'); 
    });
	
});
