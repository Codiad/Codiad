<?php

function mb_ord($v) {
	$k = mb_convert_encoding($v, 'UCS-2LE', 'UTF-8'); 
	$k1 = ord(substr($k, 0, 1)); 
	$k2 = ord(substr($k, 1, 1)); 
	return $k2 * 256 + $k1; 
}

function mb_chr($num){
	return mb_convert_encoding('&#'.intval($num).';', 'UTF-8', 'HTML-ENTITIES');
}

?>
