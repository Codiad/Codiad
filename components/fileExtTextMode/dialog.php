<?php
require_once 'class.fileExtensionTextMode.php';

//check Session
$fileExTM = new CFileExtensionTextMode();

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
$ext = @Common::getJSON('fileExtensions.php');

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
echo '<button class="btn-left" onClick="codiad.fileExtTextMode.addFieldToForm()">add field... </button>';
echo '<button class="btn-right" onClick="codiad.fileExtTextMode.sendForm();"> Edit extensions </button><br/>';
echo '<button onClick="codiad.modal.unload();">'.Common::get_i18n('Close').'</button>';

//echo '</form>'."\n";
?>