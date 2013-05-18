<?php

/*
 *  (c) Codiad & ccvca (https://github.com/ccvca)
* @author ccvca (https://github.com/ccvca)
* This Code is released under the same licence as Codiad (https://github.com/Codiad/Codiad)
* See [root]/license.txt for more. This information must remain intact.
*/

require_once 'class.fileextension_textmode.php';

//check Session
$fileExTM = new fileextension_textmode();

//return a select-field with all availiable text modes, the one in the param is selected
function getTextModeSelect($extension){
	global $fileExTM;
	$extension = trim(strtolower($extension));
	$find = false;
	$ret = '<select name="textMode[]" class="textMode">'."\n";
	foreach($fileExTM->getAvailiableTextModes() as $textmode){
		$ret .= '	<option';
		if($textmode == $extension){
			$ret .= ' selected="selected"';
			$find = true;
		}
		$ret .='>'.$textmode.'</option>'."\n";
	}
	
	//unknown extension, print it in the end
	if(!$find && $extension != ''){
		$ret .= '	<option selected="selected">'.$textmode.'</option>'."\n";
	}
	
	$ret .= '</select>'."\n";
	
	return $ret;
}

$ext = false;
//ignore warnings
$ext = @Common::getJSON(fileextension_textmode::storeFilename);

if(!is_array($ext)){
	//default extensions
	$ext = $fileExTM->getDefaultExtensions();
}

$textModes = $fileExTM->getAvailiableTextModes();

if(!@ksort($ext)){
	die(json_encode(array('status' => 'error', 'msg' => 'Internal PHP error.') ));
}

//echo '<form onSubmit="return codiad.fileExtTextMode.sendForm();">'."\n";
echo '	<div id="FileExtTextModeDiv">';
echo '<label>Edit file extensions</label>';
echo '<table id="FileExtTextModeTable">';
	echo '<thead><tr><td><label>File extension</label></td>';
	echo '<td><label>Text mode</label></td></tr></thead>';
	echo '<tbody id="FileExtTextModeTableTbody">'."\n";
	foreach($ext as $ex => $mode){
		echo '<tr>';
		echo '<td><input class="FileExtension" type="text" name="extension[]" value="'.$ex.'" /></td>';
		echo '<td>'.getTextModeSelect($mode).'</td>';
		echo '</tr>'."\n";
	}
	echo '</tbody>';
echo '</table>';
echo '</div>';
echo '<button class="btn-left" onClick="codiad.fileext_textmode.addFieldToForm()">Add New Extension</button>';
echo '<button class="btn-mid" onClick="codiad.fileext_textmode.sendForm();">Save Scheme</button>';
echo '<button class="btn-right" onClick="codiad.modal.unload();">'.Common::get_i18n('Close').'</button>';

//echo '</form>'."\n";
?>