function setupeditor(){
	
	$(".editor").each(function(){
		CKEDITOR.replace(this.id,{
									customConfig 				: '/js/admin/ckeconfig.js',
									filebrowserBrowseUrl 		: '/admin/filebrowser/index',
									filebrowserImageBrowseUrl  	: '/admin/imagebrowser/index'}
		);
   });
}

function initAdmin(){
	
	// setup sortable tables
	
	$('.sortable_table').tableDnD({
		onDragClass: "selectedrow",
        onDrop: function(table, row) {
			params = $.tableDnD.serialize();
			$('#save_sort_val').val(params);
			$('#save_sort').removeAttr("disabled");
			$('#save_sort').addClass("buttons");
			alternateTableRows("sort","even","oddd");
        }
    });
	
	if ($('.sortable_table').length != 0){
		$('.sortable_table td:nth-child(2)').hide();
		$('.sortable_table th:nth-child(2)').hide();		
	}
	
	// setup CKE editor
	
	setupeditor();
	
	// setup datepicker
	
	$(".jqdatepicker").datepicker({ dateFormat: 'dd-mm-yy' });
}



