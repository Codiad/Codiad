/*
	* Copyright (c) Codiad & Andr3as, distributed
	* as-is and without warranty under the MIT License.
	* See http://opensource.org/licenses/MIT for more information. 
	* This information must remain intact.
	*/

(function(global, $) {

	var codiad = global.codiad,
		scripts = document.getElementsByTagName('script'),
		path = scripts[scripts.length - 1].src.split('?')[0],
		curpath = path.split('/').slice(0, -1).join('/') + '/';

	$(function() {
		codiad.Closure.init();
	});

	codiad.Closure = {

		path: curpath,
		base: "",
		closure: null, //Sass instance
		imports: [],
		promises: [],

		init: function() {
			var _this = this;
			closure = {
				options: ''
			};

			amplify.subscribe('context-menu.onShow', function(obj) {
				if (/(\.js)$/.test(obj.path)) {
					$('#context-menu').append('<hr class="file-only js">');
					$('#context-menu').append('<a class="file-only js" onclick="codiad.Closure.contextMenu($(\'#context-menu\').attr(\'data-path\'));"><span class="icon-code"></span>Compile JS</a>');
				}
			});
			amplify.subscribe('context-menu.onHide', function() {
				$('.js').remove();
			});
		},

		contextMenu: function(path) {
			var file = this.__filename(path);
			path = this.__dirname(path);
			this.processFile(file, path);
		},

		compile: function(callback) {
			var _this = this;
			_this.setSettings(path);
			console.log(path);
			var code = '';
			var post_data = '';
			if(_this.imports) {
				code = _this.imports.map(function(t) {
					var tmp = { code: t.content };
					post_data += post_data ? ',"js_code' + '":"' + tmp.code.toString() + '"' : '"js_code' + '":"' + t.content.toString() + '"';
					return t.content;
				});
			}   
			post_data = '{' + post_data + '}';
			console.log(post_data);
			post_data = JSON.parse(post_data);
			console.log(post_data);
			
			// var post_data = $.param({js_code: code,
			// 	compilation_level: 'WHITESPACE_ONLY',
			// 	output_format: 'text',
			// 	output_info: 'compiled_code'		
			// }, true);
			
			// console.log(post_data);
			
			// post_data

			// var post_data = {
			// 	js_code: code[0],
			// 	compilation_level: 'SIMPLE_OPTIMIZATIONS',
			// 	output_format: 'json',
			// 	output_info: 'compiled_code',
			// 	formatting: 'pretty_print'
			// };
			
			$.ajax({
				url: 'https://closure-compiler.appspot.com/compile',
				type: "POST",
				// contentType: 'application/json',
				data: post_data,
				success: function(result) {
					console.log('success');
					console.log(result);
				},
				error: function(response) {
					console.log('fail');
					console.log(JSON.parse(response.responseText).compiledCode);
				}
			});
		},

		getIndentation: function() {
			if (codiad.editor.settings.softTabs) {
				var length = parseInt(codiad.editor.settings.tabSize, 10);
				var indent = "";
				for (var i = 0; i < length; i++) {
					indent += " ";
				}
				return indent;
			} else {
				return "\t";
			}
		},

		processFile: function(file, path) {
			var _this = this;
			this.base = this.__dirname(path);
			$.getJSON(this.path + 'controller.php?action=getContent&path=' + path + '/' + file, function(json) {
				if (json.status == "success") {

					_this.imports = [{
						file: file,
						path: path,
						content: json.content
					}];

					_this.scanForImports(json.content, path);
					
					Promise.all(_this.promises).then(function() {
						_this.compile(function(result) {
							//Catch errors
							if (result.status === 0) {
								console.log(result);
								console.log(_this.path + " | " + path);
								// $.post(_this.path + 'controller.php?action=saveContent&path=' + path, {
								// 	content: result.text
								// }, function(response) {
								// 	response = JSON.parse(response);
								// 	if (response.status == "success") {
								// 		codiad.filemanager.rescan($('#project-root').attr('data-path'));
								// 	}
								// 	codiad.message[response.status](response.message);
								// });
							} else {
								console.log(result);
								codiad.message.error(result.message + " on Line " + result.line + " Column " + result.column);
							}
						});
					});
				} else {
					codiad.message.error(json.message);
				}
			});
		},

		scanForImports: function(js, path) {
			var _this = this;
			var imports = js.match(/import( .*)from '.*\.js';/g);
			if (imports && imports.length > 0) {
				imports = imports.map(function(imp) {
					imp = imp.match(/'.*'/)[0];
					return imp.replace(/'/g, '');
				});
				imports.forEach(function(imp) {
					_this.retrieveFile(imp, path);
				});				
			} else {
				_this.promises.push(setTimeout(function() {}, 100));
			}
		},

		retrieveFile: function(file, path) {
			var _this = this;
			_this.promises.push($.getJSON(this.path + 'controller.php?action=getContent&path=' + path + '/' + file, function(json) {
				if (json.status == "success") {
					_this.imports.push({
						file: file,
						path: path,
						content: json.content
					});
				} else {
					codiad.message.error(json.message);
				}
			}));
		},

		setSettings: function(path) {
			var _this = this;
			// this.closure.options({
			//     // style: Sass.style.expanded,
			//     style: closure.style.compressed,
			//     indent: _this.getIndentation(),
			//     // sourceMap: true,
			//     // sourceMapFile: path + '.map',
			//     // sourceMapContents: false,
			//     // sourceMapEmbed: false,
			//     // sourceMapOmitUrl: false,
			//     // inputPath: 'stdin',
			//     // outputPath: 'stdout'
			// }, function(){});
		},

		__filename: function(path, suffix) {
			//  discuss at: http://phpjs.org/functions/basename/
			var b = path;
			var lastChar = b.charAt(b.length - 1);

			if (lastChar === '/' || lastChar === '\\') {
				b = b.slice(0, -1);
			}

			b = b.replace(/^.*[\/\\]/g, '');

			if (typeof suffix === 'string' && b.substr(b.length - suffix.length) == suffix) {
				b = b.substr(0, b.length - suffix.length);
			}

			return b;
		},

		__dirname: function(path) {
			// discuss at: http://phpjs.org/functions/dirname/
			return path.replace(/\\/g, '/')
				.replace(/\/[^\/]*\/?$/, '');
		}

	};
})(this, jQuery);