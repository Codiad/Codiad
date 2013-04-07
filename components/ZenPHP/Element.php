<?php

class Element {
	
	private $tagName;
	private $attrs = array();
	private $parent = null;
	private $children = array();
	private $isEmptyTag = false;
	
	
	private $tabString = "    ";
	
	function __construct($tagName, $parent = null, $isEmptyTag = false) {
		$this->tagName = $tagName;
		$this->isEmptyTag = $isEmptyTag;
		if ($parent instanceof Element)
			$this->parent = $parent;
	}
	
	function addChild(Element $child) {
		$this->children[] = $child;
		$child->setParent($this);
	}
	
	function getTabString($count) {
		if ($count > 0)
			return str_repeat($this->tabString, $count);
		return '';
	}
	
	
	function parse($tabCount = 0) {
		$tabs    = $this->getTabString($tabCount);
		$attrStr = $this->parseAttrs();
		$lTag    =  '<' . $this->tagName . $attrStr;
		if ($this->isEmptyTag) {
			$lTag .= " />\n";
			return $tabs . $lTag;
		} else
			$lTag .= ">";		
		
		$content = '';
		if (!empty($this->children)) {
			foreach ($this->children as $child) {
				$content .= $child->parse($tabCount + 1);
			}
			$content .= $tabs;
		}
		if (!empty($content))
			$lTag .= "\n";
		
		$rTag = '</' . $this->tagName . '>';
		
		return $tabs . $lTag . $content . $rTag . "\n";
	}	
	
	protected function parseAttrs() {
		if (empty($this->attrs))
			return '';
		$attrs = array();
		foreach ( $this->attrs as $attr => $value ) {
			$attrs[] = $attr . '="' . $value . '"';
		}
		return ' ' . implode(' ', $attrs);
	}
	
	function attr($name, $value) {
		$this->attrs[$name] = $value;
	}
	
	public function getTagName() {
		return $this->tagName;
	}

	public function getAttrs() {
		return $this->attrs;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getChildren() {
		return $this->children;
	}

	public function setTagName($tagName) {
		$this->tagName = $tagName;
	}

	public function setAttrs($attrs) {
		$this->attrs = $attrs;
	}

	public function setParent($parent) {
		$this->parent = $parent;
	}

	public function setChildren($children) {
		$this->children = $children;
	}	
	
}
