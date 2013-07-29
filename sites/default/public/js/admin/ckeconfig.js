/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'nl';
	//config.uiColor = '#ff6d06';
	config.uiColor = '#ddd';
	
	config.toolbar = 'LS';
	config.height  = 500;
	config.extraPlugins = 'MediaEmbed';
	config.forcePasteAsPlainText = true;

	config.toolbar_LS =
	[
		['Source'],
		['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Bold','Italic','Underline'],['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Subscript','Superscript','-','SpecialChar','PageBreak','NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		['Image','Link','Unlink','Anchor'],
		['SpecialChar'],
		['Format','Font','FontSize'],
		['Maximize', 'ShowBlocks']
	];
	
	config.skin = 'kama';


};

