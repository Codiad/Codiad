<?php
require_once(dirname(__FILE__) . '/RootElement.php');
require_once(dirname(__FILE__) . '/TextElement.php');

class ZenPHP {
	
	private static $emptyTags = array(
		'br',
		'hr',
		'meta',
		'link',
		'base',
		'link',
		'meta',
		'hr',
		'br',
		'img',
		'embed',
		'param',
		'area',
		'col',
		'input',
	);
	
	private function __construct(){}
	
	
	static function expand($seletor, $data = array()) {
		$parts = preg_split('#(>|\+(?=.+)|<)#is', $seletor, - 1, PREG_SPLIT_DELIM_CAPTURE);
		$root = new RootElement();
		$current = $root;
		$last = null;
		foreach ( $parts as $part ) {
			if (empty($part))
				continue;
			if (is_null($current))
				return '';
			if ($part == '+') {
				continue;
			} else if ($part == '>') {
				$current = $last;
			} else if ($part == '<') {
				$current = $current->getParent();
			} else {
				$element = self::createElement($part, $data);
				if (is_null($element))
					return '';
				$current->addChild($element);
				$last = $element;
			}
		
		}
		
		return $root->parse();
	}
	
	private static function createElement($seletor, $data) {
		if (preg_match('/^\{(.+)\}$/is', trim($seletor), $content)) {
			return new TextElement(self::replaceVars($content[1], $data));
		} 
		
		if (preg_match_all('/([a-z0-9]+)(#[^\.\[\{]+)?(\.[^\[\{]+)?(\[.+\])?(?:\{(.+)\})?/is', $seletor, $matches, PREG_SET_ORDER)) {
			/*
			 * 1 - tagName
			 * 2 - id
			 * 3 - classes
			 * 4 - attributes
			 * 5 - content
			 * */
			$matches = $matches[0];
			$htmlTag = $matches[1];
			$element = new Element($htmlTag, null, self::isEmptyTag($htmlTag));
			
			if (isset($matches[2]) && !empty($matches[2])) {
				$element->attr('id', trim($matches[2], '#'));
			}
			
			if (isset($matches[3]) && !empty($matches[3])) {
				$classes = implode(' ', explode('.', trim($matches[3], '.')));
				$element->attr('class', $classes);
			}
			
			if (isset($matches[4]) && !empty($matches[4])) {
				if (!preg_match_all('/\[([^=]+)=(.*?)\]/is', $matches[4], $attrs, PREG_SET_ORDER))
					return null;
				foreach ($attrs as $attr) {
					$value = self::replaceVars($attr[2], $data);
					$element->attr($attr[1], $value);
				}
			}
			
			if (isset($matches[5]) && !empty($matches[5])) {
				$content = self::replaceVars($matches[5], $data);
				$element->addChild(new TextElement($content));				
			}
			
			return $element;
		}
		return null;
	}
	
	private static function replaceVars($str, $vars) {
		if (empty($vars))
			return $str;
		if (preg_match_all('/%(.*?)%/', $str, $ph)) {
			$phVars = $ph[1];
			foreach ($phVars as $var) {
				if (isset($vars[$var]))
					$str = str_replace('%' . $var . '%', $vars[$var], $str);
			}
		}
		return $str;
	}	
	
	private static function isEmptyTag($tag) {
		return in_array(strtolower($tag), self::$emptyTags);
	}
	
}
/*
$vars = array('id' => 1, 'title' => 'Post Title', 'content' => 'Post Content');
echo ZenPHP::expand('div.post>h1>a[href=posts.php?id=%id%]{%title%}<p{%content%}', $vars);
*/