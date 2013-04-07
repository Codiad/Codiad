<?php
require_once(dirname(__FILE__) . '/Element.php');
class TextElement extends Element {
	
	private $content;
	
	function __construct($content, $parent = null) {
		$this->setParent($parent);
		$this->content = $content;
	}
	
	function addChild($child){
		if ($this->getParent() != null)
			$this->getParent()->addChild($child);
	}

	function parse($tabCount = 0) {
		$tabs = $this->getTabString($tabCount);
		return $tabs . implode("\n" . $tabs, explode("\n", $this->content)) . "\n";
	}
	
}


