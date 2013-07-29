/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'nl';
	config.uiColor = '#ddd';
	config.height  = 200;
		
	config.toolbar = 'LS';
	config.toolbarStartupExpanded = false;
	config.extraPlugins = 'MediaEmbed';
	config.forcePasteAsPlainText = true;

	/*
	config.toolbar_LS =
	[
		['Save','Format','Cut','Copy','Paste','Bold','Italic','Underline',
		'Subscript','Superscript','SpecialChar','NumberedList','BulletedList','Image','Link','Unlink']
	];
	*/
	
	config.toolbar_LS =
		[
			['Save','Source'],
			['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
			['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
			['Bold','Italic','Underline'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
			['Subscript','Superscript','-','SpecialChar','PageBreak','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
			['Link','Unlink','Anchor'],
			['SpecialChar'],['Maximize', 'ShowBlocks']
		];
	
	config.skin = 'kama';


};
