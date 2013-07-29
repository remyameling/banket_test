$(document).ready(function(){
		
	// ====================================================================================================================================
	// article edit
	// ====================================================================================================================================
	
	$(".article_content_edit").each(function(){		// vervang elementen met class="article_content_edit", door een formulier met textarea 
		
		html_content = $(this).html();
		url          = $(this).attr("rel");
		field        = "article_"+$(this).attr("field");
		
		frm_head     = '<form frmid="editor" class="inline_edit_form" method="post" action="'+url+'">';
		frm_id       = '<input type="hidden" name="frmid" value="frmarticle" />';
		txt_head     = '<textarea name="'+field+'" id="article_'+$(this).attr('id')+'" cols="200" rows="27">';
		txt_foot     = '</textarea>';
		frm_foot	  = '</form>';
		
		new_content  = frm_head+frm_id+txt_head+html_content+txt_foot+frm_foot;
		
		$(this).html("");
		$(this).prepend(new_content);
	});
	
	
	
	$(".article_content_edit").dialog({	// vervang alle inline editors door jQuery dialogen met autoOpen = false
		autoOpen	: false,
		width		: 1000,
		height		: 700,
		modal		: true,
		close		: function(event, ui) { // detroy CKeditor bij sluiten dialoog
						ta_id = $("#"+$(this).attr('id')+" textarea").attr('id');
						CKEDITOR.instances[ta_id].destroy();
					  }
	});
	
	$(".open_edit")
		.button({text:false,icons:{primary:"ui-icon-pencil"}})
		.click(function() {	// open dialog bij klikken op .open_edit elementen		
			id 		= $(this).attr('rel');
			ta_id 	= $("#"+id+" textarea").attr('id');
			
			CKEDITOR.replace(ta_id,{
									customConfig 				: '/js/default/ckeconfig.js',
									filebrowserBrowseUrl 		: '/admin/filebrowser/index',
									filebrowserImageBrowseUrl  	: '/admin/imagebrowser/index'}
			);
			
			$("#"+id ).dialog("open");
			return false;
	});	
	
	// ====================================================================================================================================
	// article delete
	// ====================================================================================================================================
	
	$(".article_delete").dialog({	// vervang artikle_delete div's door dialoog
		autoOpen	: false,
		modal		: true,
		buttons: { 'Verwijderen': function() {
						window.location = $(this).attr('rel');
						$(this).dialog("close");
					},
					'Annuleren': function() {
						$(this).dialog( "close" );
					}
				 }
	});
	
	
	$(".open_delete")
		.button({text:false,icons:{primary:"ui-icon-trash"}})
		.click(function() {  // open dialog bij klikken op .open_delete elementen
			id = $(this).attr('rel');
			$("#"+id ).dialog("open");
		});
	
	// ====================================================================================================================================
	// article add
	// ====================================================================================================================================
	
	$(".article_add").dialog({	// vervang artikle_add div's door dialoog
		autoOpen	: false,
		modal		: true,
		width		: 500,
		height		: 200
	});
	
	
	function postAddForm(form){	// helper funktie om een ArticlaAdd formulier middels ajax te posten
							
		url 				= form.attr('action')+"/layout/false";
		article_title		= $("#article_title",form).val();
		article_uniquename  = $("#article_uniquename",form).val();
		frmid			    = $("#frmid",form).val();
		
		$.ajax({
  				url		: url,
				type	: "POST",
				data	: {
							article_title:article_title,
						   	article_uniquename:article_uniquename,
						   	frmid:frmid,
							async: false

						   }
		});				
	}
	
	$(".article_add").each(function(){	// voeg een ajaxSuccess handler toe aan alle .articleAdd divs
		$(this).ajaxSuccess(function(e, xhr, settings) {
			
			if (xhr.responseText.substring(0,6) == 'OKSUCC')	// artikel succesvol toegevoegd; sluit dialoog
			{
				id = $(this).attr('id');	
				$("#"+id ).dialog("close");							// close dialog
				
				window.location = xhr.responseText.substring(6);	// redirect naar nieuw artikel 
			}
			else
			{
				$(this).html(xhr.responseText);	// formulier met event. foutmeldingen opnieuw tonen
			}
			
			// opnieuw toevoegen click handler aan submit button
			
			$("#save",this).click(function(){ 
					
				form            	= $(this).parent().parent().parent();
				postAddForm(form);
					
				return false;
			});								
		});
	});
	
	$(".article_add").each(function(){	// toevoegen click handler aan formulier submit button
		$("#save",this).click(function(){ 
			
			form            	= $(this).parent().parent().parent();
			postAddForm(form);
			
			return false;
		});		
	});
	
	
	$(".open_add")
		.button({text:"weblog",icons:{primary:"ui-icon-plusthick"}})
		.click(function() { 	// open dialog bij klikken op .open_delete elementen
			id = $(this).attr('rel');
			$("#"+id ).dialog("open");
	});
		
	// ====================================================================================================================================
	// article title
	// ====================================================================================================================================
	
	$(".article_title").dialog({	// vervang article_title div's door dialoog
		autoOpen	: false,
		modal		: true,
		width		: 300,
		height		: 200
	});
	
	$(".open_title")
		.button({text:false,icons:{primary:"ui-icon-tag"}})		
		.click(function() { 		// open dialog bij klikken op .open_delete elementen
			id = $(this).attr('rel');
			$("#"+id ).dialog("open");
		
	});
	
	// ====================================================================================================================================
	// article sort up
	// ====================================================================================================================================
	
	$(".open_sortup").button({text:false,icons:{primary:"ui-icon-arrowthick-1-n"}})
	
	// ====================================================================================================================================
	// article sort down
	// ====================================================================================================================================
	
	$(".open_sortdown").button({text:false,icons:{primary:"ui-icon-arrowthick-1-s"}})
	
	
	
	
	
});