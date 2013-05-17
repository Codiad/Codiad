var debug;
(function(global, $) {

	$(function() {
		codiad.fileExtTextMode.init();
	});

	global.codiad.fileExtTextMode = {

		pluginDir:'components/FileExtTextMode/',
		
		availableTextModes :[
		        'abap',
		        'asciidoc',
		        'c9search',
		        'c_cpp',
		        'clojure',
		        'coffee',
		        'coldfusion',
		        'csharp',
		        'css',
		        'curly',
		        'dart',
		        'diff',
		        'django',
		        'dot',
		        'ftl',
		        'glsl',
		        'golang',
		        'groovy',
		        'haml',
		        'haxe',
		        'html',
		        'jade',
		        'java',
		        'javascript',
		        'json',
		        'jsp',
		        'jsx',
		        'latex',
		        'less',
		        'liquid',
		        'lisp',
		        'livescript',
		        'logiql',
		        'lsl',
		        'lua',
		        'luapage',
		        'lucene',
		        'makefile',
		        'markdown',
		        'mushcode',
		        'objectivec',
		        'ocaml',
		        'pascal',
		        'perl',
		        'pgsql',
		        'php',
		        'powershell',
		        'python',
		        'r',
		        'rdoc',
		        'rhtml',
		        'ruby',
		        'sass',
		        'scad',
		        'scala',
		        'scheme',
		        'scss',
		        'sh',
		        'sql',
		        'stylus',
		        'svg',
		        'tcl',
		        'tex',
		        'text',
		        'textile',
		        'tmsnippet',
		        'toml',
		        'typescript',
		        'vbscript',
		        'velocity',
		        'xml',
		        'xquery',
		        'yaml'
		    ],
		
		init : function() {
			this.initEditorFileExtensionTextModes();
		},

		formWidth : 300,

		open : function() {
			codiad.modal.load(this.formWidth,
					this.pluginDir+'fileExtTextModeForm.php');
			codiad.modal.hideOverlay();
		},
		
		sendForm : function(){
			var $div = $('#FileExtTextModeDiv');
			var extensions = $div.find('.FileExtension');
			var formData = {'extension[]' : [], 'textMode[]' : []};
			for(var i = 0; i <  extensions.size(); ++i){
				formData['extension[]'].push(extensions[i].value);
			}
			
			var textMode = $div.find('.textMode');
			for(var i = 0; i <  textMode.size(); ++i){
				formData['textMode[]'].push(textMode[i].value);
			}
			
			$.post(this.pluginDir+'process.php', formData, codiad.fileExtTextMode.setEditorFileExtensionTextModes);
			
			codiad.modal.unload();
		},
		
		addFieldToForm : function(){
			var $div = $('#FileExtTextModeDiv');
			
			var code = '<input class="FileExtension" style="width: 100px; display: inline;" type="text" name="extension[]" value="" /> &nbsp;&nbsp;';
			code += '<select style="width: 100px; display: inline;" name="textMode[]" class="textMode">';
			for(var i = 0; i < this.availableTextModes.length; ++i){
				code += '<option>'+ this.availableTextModes[i] +'</option>';
			}
			code += '</select><br/>';
	
			$div.append(code);
			
			//scroll as far down as possible
			$div.scrollTop(1000000);
		},
		
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
		},
		
		initEditorFileExtensionTextModes : function(){
			$.get(this.pluginDir+'getFileExtTextModes.php', {}, this.setEditorFileExtensionTextModes);
		},
		
		setEditorFileExtensionTextModes : function(data){
			resp = $.parseJSON(data);
			if(resp.status != 'error' && resp.extensions != undefined){
				codiad.editor.clearFileExtensionTextMode();
				
				for(i in resp.extensions){
					codiad.editor.addFileExtensionTextMode(i, resp.extensions[i]);
				}
			}
			codiad.fileExtTextMode.showStatus(data);
		}
		

	};
})(this, jQuery);