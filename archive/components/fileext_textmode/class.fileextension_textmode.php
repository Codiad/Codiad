<?php

/*
 *  (c) Codiad & ccvca (https://github.com/ccvca)
 * @author ccvca (https://github.com/ccvca)
 * This Code is released under the same licence as Codiad (https://github.com/Codiad/Codiad)
 * See [root]/license.txt for more. This information must remain intact.
 */

require_once '../../common.php';


class fileextension_textmode
{

    //////////////////////////////////////////////////////////////////
    //default associations
    //////////////////////////////////////////////////////////////////
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
            'java' => 'java',
            'xml' => 'xml',
            'sql' => 'sql',
            'md' => 'markdown',
            'c' => 'c_cpp',
            'cpp' => 'c_cpp',
            'd' => 'd',
            'h' => 'c_cpp',
            'hpp' => 'c_cpp',
            'py' => 'python',
            'rb' => 'ruby',
            'erb' => 'html_ruby',
            'jade' => 'jade',
            'coffee' => 'coffee',
            'vm' => 'velocity');

    //////////////////////////////////////////////////////////////////
    //availiable text modes
    //////////////////////////////////////////////////////////////////
    private $availiableTextModes = array(
            'abap',
            'abc',
            'actionscript',
            'ada',
            'apache_conf',
            'applescript',
            'asciidoc',
            'assembly_x86',
            'autohotkey',
            'batchfile',
            'c9search',
            'c_cpp',
            'cirru',
            'clojure',
            'cobol',
            'coffee',
            'coldfusion',
            'csharp',
            'css',
            'curly',
            'd',
            'dart',
            'diff',
            'django',
            'dockerfile',
            'dot',
            'eiffel',
            'ejs',
            'elixir',
            'elm',
            'erlang',
            'forth',
            'ftl',
            'gcode',
            'gherkin',
            'gitignore',
            'glsl',
            'gobstones',
            'golang',
            'groovy',
            'haml',
            'handlebars',
            'haskell',
            'haxe',
            'html',
            'html_elixir',
            'html_ruby',
            'ini',
            'io',
            'jack',
            'jade',
            'java',
            'javascript',
            'json',
            'jsoniq',
            'jsp',
            'jsx',
            'julia',
            'latex',
            'lean',
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
            'mask',
            'matlab',
            'maze',
            'mel',
            'mips_assembler',
            'mushcode',
            'mysql',
            'nix',
            'nsis',
            'objectivec',
            'ocaml',
            'pascal',
            'perl',
            'pgsql',
            'php',
            'plain_text',
            'powershell',
            'praat',
            'prolog',
            'protobuf',
            'python',
            'r',
            'razor',
            'rdoc',
            'rhtml',
            'rst',
            'ruby',
            'rust',
            'sass',
            'scad',
            'scala',
            'scheme',
            'scss',
            'sh',
            'sjs',
            'smarty',
            'snippets',
            'soy_template',
            'space',
            'sql',
            'sqlserver',
            'stylus',
            'svg',
            'swift',
            'swig',
            'tcl',
            'tex',
            'text',
            'textile',
            'toml',
            'twig',
            'typescript',
            'vala',
            'vbscript',
            'velocity',
            'verilog',
            'vhdl',
            'wollok',
            'xml',
            'xquery',
            'yaml'
    );

    const storeFilename = 'extensions.php';
    
    //////////////////////////////////////////////////////////////////
    //check the session if the user is allowed to do anything here
    //////////////////////////////////////////////////////////////////
    public function __construct()
    {
        Common::checkSession();
    }

    public function getAvailiableTextModes()
    {
        return $this->availiableTextModes;
    }

    public function getDefaultExtensions()
    {
        return $this->defaultExtensions;
    }

    //////////////////////////////////////////////////////////////////
    //checks if the sended extensions are valid to prevent any injections
    //////////////////////////////////////////////////////////////////
    public function validateExtension($extension)
    {
        return preg_match('#^[a-z0-9\_]+$#i', $extension);
    }

    //////////////////////////////////////////////////////////////////
    //checks if the sended extensions are valid to prevent any injections and usage of removed text modes
    //////////////////////////////////////////////////////////////////
    public function validTextMode($mode)
    {
        return in_array($mode, $this->availiableTextModes);
    }

    //////////////////////////////////////////////////////////////////
    //process the form with the associations
    //////////////////////////////////////////////////////////////////
    private function processFileExtTextModeForm()
    {
        if (!Common::checkAccess()) {
            return array('status' =>'error', 'msg' =>'You are not allowed to edit the file extensions.');
        }
        //Store Fileextensions and Textmodes in File:
        if (!isset($_POST['extension']) || !is_array($_POST['extension'])
                || !isset($_POST['textMode']) || !is_array($_POST['textMode'])) {
            return json_encode(array('status' => 'error', 'msg' => 'incorrect data send'));
        }

        $exMap = array();

        $warning = '';

        //Iterate over the sended extensions
        foreach ($_POST['extension'] as $key => $extension) {
            //ignore empty extensions, so that they are going to removed
            if (trim($extension) == '') {
                continue;
            }

            //get the sended data and check it
            if (!isset($_POST["textMode"][$key])) {
                return json_encode(array('status' => 'error', 'msg' => 'incorrect data send.'));
            }

            $extension = strtolower(trim($extension));
            $textMode =  strtolower(trim($_POST["textMode"][$key]));
            
            if (!$this->validateExtension($extension)) {
                return json_encode(array('status' => 'error', 'msg' => 'incorrect extension:'.htmlentities($extension)));
            }

            if (!$this->validTextMode($textMode)) {
                return json_encode(array('status' => 'error', 'msg' => 'incorrect text mode:'.htmlentities($textMode)));
            }

            //data was correct and could be insert
            if (isset($exMap[$extension])) {
                $warning =  htmlentities($extension).' is already set.<br/>';
            } else {
                $exMap[$extension] = $textMode;
            }
        }

        //store the associations
        Common::saveJSON(fileextension_textmode::storeFilename, $exMap);
        if ($warning != '') {
            return json_encode(array('status' => 'warning', 'msg' => $warning, 'extensions' => $exMap ));
        } else {
            return json_encode(array('status' => 'success', 'msg' => 'File extensions are saved successfully.', 'extensions' => $exMap));
        }
    }

    //////////////////////////////////////////////////////////////////
    //process all the possible forms
    //////////////////////////////////////////////////////////////////
    public function processForms()
    {
        if (!isset($_GET['action'])) {
            return json_encode(array('status' => 'error', 'msg' => 'incorrect data send.'));
        }

        switch ($_GET['action']) {
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

    //////////////////////////////////////////////////////////////////
    //Send the default extensions
    //////////////////////////////////////////////////////////////////
    private function prcessGetFileExtTextModes()
    {
        $ext = false;
        //ignore warnings
        $ext = @Common::getJSON(fileextension_textmode::storeFilename);

        if (!is_array($ext)) {
            //default extensions
            $ext = $this->defaultExtensions;
        }
        
        //the availiable extensions, which aren't removed
        $availEx = array();
        foreach ($ext as $ex => $mode) {
            if (in_array($mode, $this->availiableTextModes)) {
                $availEx[$ex] = $mode;
            }
        }
        return json_encode(array('status' => 'success', 'extensions' => $availEx, 'textModes' => $this->availiableTextModes));
    }
    
    //////////////////////////////////////////////////////////////////
    //return a select-field with all availiable text modes, the one in the parameter is selected
    //////////////////////////////////////////////////////////////////
    public function getTextModeSelect($extension)
    {
        $extension = trim(strtolower($extension));
        $find = false;
        $ret = '<select name="textMode[]" class="textMode">'."\n";
        foreach ($this->getAvailiableTextModes() as $textmode) {
            $ret .= '	<option';
            if ($textmode == $extension) {
                $ret .= ' selected="selected"';
                $find = true;
            }
            $ret .='>'.$textmode.'</option>'."\n";
        }
        
        //unknown extension, print it in the end
        if (!$find && $extension != '') {
            $ret .= '	<option selected="selected">'.$textmode.'</option>'."\n";
        }
    
        $ret .= '</select>'."\n";
    
        return $ret;
    }
}
