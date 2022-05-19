/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.allowedContent = true;
    config.removeFormatAttributes = '';
	// config.format_tags = 'p;h1;h2;h3;pre;';
	
	config.htmlEncodeOutput = false;
	config.entities = false;
	config.disableNativeSpellChecker = false;
	config.height = 350;
	config.enterMode = 1;
	config.shiftEnterMode = 2;
	
	config.contentsCss = [ 
		'/vendors/bootstrap-3.3.7/css/bootstrap.min.css', 
		'/css/classes.css',
		'/css/ckeditor.css' 
	];
	
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];

	config.removeButtons = 'Save,NewPage,Preview,Print,Form,Checkbox,Radio,TextField,Textarea,Select,Button,HiddenField,ImageButton,Language,BidiRtl,BidiLtr,CreateDiv,Flash,Smiley,PageBreak,About,Font,FontSize,Styles,SelectAll,Replace';
};
