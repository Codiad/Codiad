<?php

require_once '../../common.php';
require_once 'defaultValues.php';

Common::checkSession();

$ext = false;
//ignore warnings
$ext = @Common::getJSON('fileExtensions.php');

if(!is_array($ext)){
	//default extensions
	$ext = $defaultExtensions;
}

echo json_encode(array('status' => 'success', 'extensions' => $ext));

?>