$(document).ready(function(){
	
	$(".dialog").dialog({
		width: 800,
		modal: true,
		autoOpen: false
	});		
	

	$(".opendialog").click(function(){		
		$("#"+$(this).attr('rel')).dialog("open");
	});
	
});