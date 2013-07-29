function filterTableOnCategory(attributename,attributevalue){	
	// filterTableOnCategory: voegt een class="hidden" toe aan
	// alle rijen die geen attribuut met de waarde value hebben
	// (wordt gebruikt door treeview)
	
	// in: attributename  (naam van het attribuut waarop gefilterd wordt)
	//     attributevalue (waarde van het attribuut)
	
	// toevoegen hidden class aan alle elementen die niet
	// voldoen aan de conditie
	$(".result tbody tr["+attributename+"!="+attributevalue+"]").each(function(){
		$(this).addClass("hidden");
	});
	// verwijder hidden class voor de elementen die wel voldoen
	$(".result tbody tr["+attributename+"="+attributevalue+"]").each(function(){
		$(this).removeClass("hidden");
	});
}

/*
function openChilds(activeClass){

	// openChilds: opent alle childs van het avtieve menu in de treeview
	// in: activeClass: class die aangeeft wat het actieve menu is

	var menu = $("#tree li."+activeClass);			
	menu.addClass("open");					// voeg "open" class toe aan alle parent li elementen van de span:
											// de jQuery treeview zorgt er dan voor dat deze geopend wordt.
}
*/

$(document).ready(function(){
	
	//openChilds("active");
	
	// verberg alle rijen van tabel	 
	filterTableOnCategory("rel","NULL");				// verberg alle sjablonen
	
	// voeg een click event toe op alle a tags van #tree
	$(".treeview a[rel]").each(function() {
		$(this).click(function(){
		    
			// filter tabel: op "rel=<id>"
			filterTableOnCategory("rel",$(this).attr('rel'));
						
			// toevoegen .selected class aan de nieuwe geselecteerde
			$(this).parent().addClass("selected");
	   	});
	});	
});