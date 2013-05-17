<?php
require_once '../../common.php';
require_once 'defaultValues.php';

Common::checkSession();

//return a select-field with all availiable text modes, the one in the param is selected
function getTextModeSelect($extension){
	global $availiableTextModes;
	$extension = trim(strtolower($extension));
	$find = false;
	$ret = '<select style="width: 100px; display: inline;" name="textMode[]" class="textMode">'."\n";
	foreach($availiableTextModes as $textmode){
		$ret .= '	<option';
		if($textmode == $extension){
			$ret .= ' selected="selected"';
			$find = true;
		}
		$ret .='>'.$textmode.'</option>'."\n";
	}
	
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
	$ext = $defaultExtensions;
}

if(!@ksort($ext) || !@ksort($availiableTextModes)){
	die(json_encode(array('status' => 'error', 'msg' => 'Internal PHP error.') ));
}

//echo '<form onSubmit="return codiad.fileExtTextMode.sendForm();">'."\n";
echo '	<div id="FileExtTextModeDiv" style="height: 300px; overflow-y: scroll; min-height: 300px;">';

	foreach($ext as $ex => $mode){
		echo '<input class="FileExtension" style="width: 100px; display: inline;" type="text" name="extension[]" value="'.$ex.'" /> &nbsp;&nbsp;';
		echo getTextModeSelect($mode) . "<br/>\n";
	}

echo '	</div>';
echo '<a onClick="codiad.fileExtTextMode.addFieldToForm()">add field... </a>'."<br/>\n";
echo '<input type="submit" onClick="codiad.fileExtTextMode.sendForm();" />';
//echo '</form>'."\n";
?>