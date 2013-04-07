<?php
require_once(dirname(__FILE__) . '/Element.php');
class RootElement extends Element {
	
	function __construct(){}
	
	function parse($tabCount = 0) {
		$content = '';
		foreach ($this->getChildren() as $child) {
			$content .= $child->parse($tabCount);
		}
		return $content;
	}
}