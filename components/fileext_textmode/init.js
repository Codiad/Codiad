/*
 *  (c) Codiad & ccvca (https://github.com/ccvca)
 * @author ccvca (https://github.com/ccvca)
 * This Code is released under the same licence as Codiad (https://github.com/Codiad/Codiad)
 * See [root]/license.txt for more. This information must remain intact.
 */

(function(global, $) {
	var self = null;
	
	$(function() {
		codiad.fileext_textmode.init();
	});

	global.codiad.fileext_textmode = {

		dialog: 'components/fileext_textmode/dialog.php',
		controller: 'components/fileext_textmode/controller.php',
		
		//////////////////////////////////////////////////////////////////
		//do a post request for the first associations of fileextensions and textmodes
        //////////////////////////////////////////////////////////////////
		availableTextModes : [],
		
		init : function() {
			self = this;
			this.initEditorFileExtensionTextModes();

			amplify.subscribe('settings.dialog.save', function(){
				if ($('#FileExtTextModeDiv:visible').length !== 0) {
					self.sendForm();
				}
			});
		},
		
        //////////////////////////////////////////////////////////////////
		//do a post request for the first associations of fileextensions and textmodes
        //////////////////////////////////////////////////////////////////
		initEditorFileExtensionTextModes : function(){
			$.get(this.controller, {'action' : 'GetFileExtTextModes'}, this.setEditorFileExtensionTextModes);
		},
		
        //////////////////////////////////////////////////////////////////
		//initial method to get the stored associations
        //////////////////////////////////////////////////////////////////
		setEditorFileExtensionTextModes : function(data){
			resp = $.parseJSON(data);
			if(resp.status != 'error' && resp.extensions != undefined){
				codiad.editor.clearFileExtensionTextMode();
				
				for(i in resp.extensions){
					codiad.editor.addFileExtensionTextMode(i, resp.extensions[i]);
				}
				
				if(resp.textModes != undefined && resp.textModes != []){
					self.availableTextModes = resp.textModes;
				}
				
				/* Notify listeners. */
	            amplify.publish('fileext_textmode.loadedExtensions');
			}
			self.showStatus(data);
		},
		
		formWidth : 400,

        //////////////////////////////////////////////////////////////////
        // Open the component dialog
        //////////////////////////////////////////////////////////////////
		open : function() {
			//codiad.modal.unload();
			codiad.modal.load(this.formWidth,
					this.dialog+"?action=fileextension_textmode_form");
			codiad.modal.hideOverlay();
		},
        //////////////////////////////////////////////////////////////////
		//send the insert extesions and textmodes to the server.
        //////////////////////////////////////////////////////////////////
		sendForm : function(){
			var $div = $('#FileExtTextModeDiv');
			var extensions = $div.find('.FileExtension');
			//data to send
			var formData = {'extension[]' : [], 'textMode[]' : []};
			for(var i = 0; i <  extensions.size(); ++i){
				formData['extension[]'].push(extensions[i].value);
			}
			
			var textMode = $div.find('.textMode');
			for(var i = 0; i <  textMode.size(); ++i){
				formData['textMode[]'].push(textMode[i].value);
			}
			
			$.post(this.controller+'?action=FileExtTextModeForm', formData, self.setEditorFileExtensionTextModes);
		},
        //////////////////////////////////////////////////////////////////
		//Add a new insert line to the form
        //////////////////////////////////////////////////////////////////
		addFieldToForm : function(){
			var $table = $('#FileExtTextModeTable');
			var $tbody = $('#FileExtTextModeTableTbody');
			
			var code = '<tr><td><input class="FileExtension" type="text" name="extension[]" value="" /></td>';
			code += '<td><select name="textMode[]" class="textMode">';
			for(var i = 0; i < this.availableTextModes.length; ++i){
				code += '<option>'+ this.availableTextModes[i] +'</option>';
			}
			
			code += '</select></td></tr>';
			
			$tbody.append(code);
			
			//scroll as far down as possible
			$table.scrollTop(1000000);
		},
		
        //////////////////////////////////////////////////////////////////
		//showing the status and msg from a http-request
        //////////////////////////////////////////////////////////////////
		showStatus : function(resp) {
			resp = $.parseJSON(resp);
			if(resp.status != undefined && resp.status != '' && resp.msg != undefined && resp.message != ''){
				switch (resp.status) {
				case 'success':
					codiad.message.success(resp.msg);
					break;
				case 'error':
					codiad.message.error(resp.msg);
					break;
				case 'notice':
					codiad.message.notice(resp.msg);
					break;
				};	
			}
		}
	};
})(this, jQuery);