function setupeditor(){
	
	$(".editor").each(function(){
		
		CKEDITOR.replace(this.id,{
									customConfig 				: '/js/default/ckeconfig.js',
									filebrowserBrowseUrl 		: '/admin/filebrowser/index',
									filebrowserImageBrowseUrl  	: '/admin/imagebrowser/index'}
		);
   });
}


$(document).ready(function(){
	setupeditor();
});