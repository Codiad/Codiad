<?php

/*
 *  (c) Codiad & ccvca (https://github.com/ccvca)
 * @author ccvca (https://github.com/ccvca)
 * This Code is released under the same licence as Codiad (https://github.com/Codiad/Codiad)
 * See [root]/license.txt for more. This information must remain intact.
 */

require_once '../../common.php';


class fileextension_textmode{

	private $defaultExtensions = array(
			'html' => 'html',
			'htm' => 'html',
			'tpl' => 'html',
			'js' => 'javascript',
			'css' => 'css',
			'scss' => 'scss',
			'sass' => 'scss',
			'less' => 'less',
			'php' => 'php',
			'php4' => 'php',
			'php5' => 'php',
			'phtml' => 'php',
			'json' => 'json',
			'xml' => 'xml',
			'sql' => 'sql',
			'md' => 'markdown',
			'c' => 'c_cpp',
			'cpp' => 'c_cpp',
			'h' => 'c_cpp',
			'hpp' => 'c_cpp',
			'py' => 'python',
			'rb' => 'ruby',
			'jade' => 'jade',
			'coffee' => 'coffee');

	private $availiableTextModes = array(
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
	);

	const storeFilename = 'extensions.php';
	
	public function __construct(){
		Common::checkSession();
	}

	public function getAvailiableTextModes(){
		return $this->availiableTextModes;
	}

	public function getDefaultExtensions(){
		return $this->defaultExtensions;
	}

	private function validateExtension($extension){
		return preg_match('#^[a-z0-9\_]+$#i', $extension);
	}

	private function validTextMode($mode){
		return in_array($mode, $this->availiableTextModes);
	}

	private function processFileExtTextModeForm(){
		//Store Fileextensions and Textmodes in File:

		if(!isset($_POST['extension']) || !is_array($_POST['extension'])
				|| !isset($_POST['textMode']) || !is_array($_POST['textMode'])){
			return json_encode(array('status' => 'error', 'msg' => 'incorrect data send'));
		}

		$exMap = array();

		$warning = '';

		foreach ($_POST['extension'] as $key => $extension){
			if(trim($extension) == '' ){
				continue;
			}

			if(!isset($_POST["textMode"][$key])){
				return json_encode(array('status' => 'error', 'msg' => 'incorrect data send.'));
			}

			$extension = strtolower(trim($extension));
			$textMode =  strtolower(trim($_POST["textMode"][$key]));

			if(!$this->validateExtension($extension)){
				return json_encode(array('status' => 'error', 'msg' => 'incorrect extension:'.htmlentities($extension)));
			}

			if(!$this->validTextMode($textMode)){
				return json_encode(array('status' => 'error', 'msg' => 'incorrect text mode:'.htmlentities($textMode)));
			}

			if(isset($exMap[$extension])){
				$warning =  htmlentities($extension).' is already set.<br/>';
			}else{
				$exMap[$extension] = $textMode;
			}
		}


		Common::saveJSON(fileextension_textmode::storeFilename, $exMap);
		if($warning != ''){
			return json_encode(array('status' => 'warning', 'msg' => $warning, 'extensions' => $exMap ));
		}else{
			return json_encode(array('status' => 'success', 'msg' => 'File extensions are saved successfully.', 'extensions' => $exMap));
		}

	}

	public function processForms(){
		if(!isset($_POST['action'])){
			return json_encode(array('status' => 'error', 'msg' => 'incorrect data send.'));
		}

		switch($_POST['action']){
			case 'FileExtTextModeForm':
				return $this->processFileExtTextModeForm();
				break;
			case 'GetFileExtTextModes':
				return $this->prcessGetFileExtTextModes();
				break;
			default:
				return json_encode(array('status' => 'error', 'msg' => 'Incorrect data send'));
				break;
		}
	}

	private function prcessGetFileExtTextModes(){
		$ext = false;
		//ignore warnings
		$ext = @Common::getJSON(fileextension_textmode::storeFilename);

		if(!is_array($ext)){
			//default extensions
			$ext = $this->defaultExtensions;
		}

		return json_encode(array('status' => 'success', 'extensions' => $ext, 'textModes' => $this->availiableTextModes));
	}
}

?>