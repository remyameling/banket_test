// JavaScript tabel functions

//
// voegt een onclick event toe op iedere tabel rij met een href attribuut
//
$(document).ready(function(){
	$("table tbody tr[href]").click(function(){
		window.location = $(this).attr("href");
	});
});

//
// roept funktie alternateTableRows aan, voor alle tabellen die een
// class "alternate" hebben
//
$(document).ready(function(){
	$("table.alternate").each(function(){
		alternateTableRows(this.id,"even","oddd");
	});
});

//
// voegt een checkall/uncheck all funktie toe aan de checkbox 
// met id = "checkall"
// alle input velden met class = "checkall" worden gedeselecteerd
//
$(document).ready(function(){
	$("#checkall").click(function(){
		
		if ($(this).attr('checked'))
			$(this).closest('table').find('.checkall').attr('checked', true);
		else
			$(this).closest('table').find('.checkall').attr('checked', false);
		
	});
});


//
// alternateTableRows: voegt classes evenClass en oddClass om-en-om toe
// aan rijen (tr) van de tabel met id=elementID.
//
function alternateTableRows(elementID,evenClass,oddClass,filterClass){	
	// in: elementID   (id van de tabel)
	//     evenClass   (classname voor 'even' rijen
	//     oddClass    (classname voor 'oneven' rijen
    //     filterClass (optioneel: indien aanwezig: laat rijen met filterClass ongemoeid)
	
	
	var classToAdd = oddClass;	// start with "oddClass"
	
	$("#"+elementID+" tbody tr").each(function(){	// foreach row of the table
											   
			$(this).removeClass(evenClass);			// remove even and odd class
			$(this).removeClass(oddClass);			// for each row
								
			var bProcess = false;					
			if (filterClass !== undefined)			// filterClass was provided
			{
				if ($(this).hasClass(filterClass))	// check if row has a class of filterClass
					bProcess = true;				// if so, process the row
			}
			else
				bProcess = true;					// filterClass not provided; process row
				
			if (bProcess)							// if this row should be processed
			{
				$(this).addClass(classToAdd);		// add class 
				
				if (classToAdd == evenClass)		// alternate class
					classToAdd = oddClass;
				else
					classToAdd = evenClass;
			}
	   	});
}