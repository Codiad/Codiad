<?php

require_once '../../common.php';
require_once 'defaultValues.php';

Common::checkSession();

//echo json_encode(array('status' => 'success', 'msg' => 'No Content.'));

//Store Fileextensions and Textmodes in File:

function validateExtension($extension){
	return preg_match('#^[a-z0-9\_]+$#i', $extension);
}

function validTextMode($mode){
	global $availiableTextModes;
	return in_array($mode, $availiableTextModes);
}

if(!isset($_POST['extension']) || !is_array($_POST['extension']) 
			|| !isset($_POST['textMode']) || !is_array($_POST['textMode'])){
	die(json_encode(array('status' => 'error', 'msg' => 'incorrect data send')));
}

$exMap = array();

$warning = '';

foreach ($_POST['extension'] as $key => $extension){
	if(trim($extension) == '' ){
		continue;
	}
	
	if(!isset($_POST["textMode"][$key])){
		die(json_encode(array('status' => 'error', 'msg' => 'incorrect data send.')));
	}
	
	$extension = strtolower(trim($extension));
	$textMode =  strtolower(trim($_POST["textMode"][$key]));
	
	if(!validateExtension($extension)){
		die(json_encode(array('status' => 'error', 'msg' => 'incorrect extension:'.htmlentities($extension))));
	}
	
	if(!validTextMode($textMode)){
		die(json_encode(array('status' => 'error', 'msg' => 'incorrect text mode:'.htmlentities($textMode))));
	}
	
	if(isset($exMap[$extension])){
		$warning =  htmlentities($extension).' is already set.<br/>';
	}else{
		$exMap[$extension] = $textMode;
	}
}


Common::saveJSON('fileExtensions.php', $exMap);
if($warning != ''){
	echo json_encode(array('status' => 'warning', 'msg' => $warning, 'extensions' => $exMap ));
}else{
	echo json_encode(array('status' => 'success', 'msg' => 'File extensions are saved successfully.', 'extensions' => $exMap));
}

?>