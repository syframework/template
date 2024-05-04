<?php
namespace Sy\Template;

class Template implements ITemplate {

	/**
	 * @var string
	 */
	private $content;

	/**
	 * @var array
	 */
	private $vars;

	/**
	 * @var array
	 */
	private $blockParsed;

	/**
	 * @var array
	 */
	private $blockCached;

	public function __construct() {
		$this->content = '';
		$this->vars = array();
		$this->blockCached = array();
		$this->blockParsed = array();
	}

	/**
	 * Sets a template content
	 *
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = preg_replace('/{\'([^\t\r\n\'\({}[":,]+)\'}/', '{"$1"}', $content);
	}

	/**
	 * Sets a template file
	 *
	 * @param string $file
	 * @throws TemplateFileNotFoundException
	 */
	public function setFile($file) {
		if (!file_exists($file)) throw new TemplateFileNotFoundException("Template file not found: $file");
		$this->setContent(file_get_contents($file));
	}

	/**
	 * Sets a value for a slot
	 *
	 * @param string $var
	 * @param string $value
	 * @param bool $append
	 */
	public function setVar($var, $value, $append = false) {
		$this->vars[$var] = ($append and isset($this->vars[$var])) ? $this->vars[$var] . $value : $value;
	}

	/**
	 * Sets a block
	 *
	 * @param string $block
	 * @param array $vars Associative array('SLOT_NAME' => 'Slot value').
	 *              Isolated vars to use for the block, use the template vars if empty
	 */
	public function setBlock($block, $vars = array()) {
		if (!$this->loadBlock($block)) return;

		$data = $this->blockCached[$block];
		if (strpos($data, '<!-- BEGIN') !== false) {
			$reg = "/[ \t]*<!-- BEGIN ([a-zA-Z0-9\._]*) -->(\s*?\n?\s*.*?\n?\s*)<!-- END \\1 -->\s*?\n?/sm";
			$data = preg_replace_callback($reg, array($this, 'getBlockContent'), $data);
		}

		$vars = empty($vars) ? $this->vars : $vars;
		$varkeys = array_keys($vars);
		$varvals =  array_map(function($v) {return (is_null($v) ? $v : str_replace(array('\\', '$'), array('\\\\', '\$'), $v));}, array_values($vars));
		$search = array_map(function($v) {return '/(?:{' . preg_quote($v) . '(?:\/[^{}\r\n]*)*})|(?:{"' . preg_quote($v) . '"})/';}, $varkeys);
		$res = preg_replace($search, $varvals, $data);
		$res = preg_replace('/{[^\t\r\n\'\({}[":,\/]+\/([^{}\r\n]*)}/', '$1', $res);

		$this->blockParsed[$block] = (isset($this->blockParsed[$block]) ? $this->blockParsed[$block] : '') . $res;
	}

	/**
	 * Returns a template render
	 *
	 * @return string
	 */
	public function getRender() {
		if (strpos($this->content, '<!-- BEGIN') !== false) {
			$reg = "/[ \t]*<!-- BEGIN ([a-zA-Z0-9\._]*) -->(\s*?\n?\s*.*?\n?\s*)<!-- END \\1 -->\s*?\n?/sm";
			$this->content = preg_replace_callback($reg, array($this, 'getBlockContent'), $this->content);
		}

		$varkeys = array_keys($this->vars);
		$varvals = array_map(function($v) {return (is_null($v) ? $v : str_replace(array('\\', '$'), array('\\\\', '\$'), $v));}, array_values($this->vars));
		$search = array_map(function($v) {return '/(?:{' . preg_quote($v) . '(?:\/[^{}\r\n]*)*})|(?:{"' . preg_quote($v) . '"})/';}, $varkeys);
		$res = preg_replace($search, $varvals, $this->content);
		$res = preg_replace('/{[^\t\r\n\'\({}[":,\/]+\/([^{}\r\n]*)}/', '$1', $res);
		$res = preg_replace('/{\"([^\t\r\n{}"]+)\"}/', '$1', $res);
		return $res;
	}

	/**
	 * Render a block
	 *
	 * @param string $block
	 * @return bool
	 */
	private function loadBlock($block) {
		if (isset($this->blockCached[$block])) return true;
		$reg = "/[ \t]*<!-- BEGIN $block -->\s*?\n?(\s*.*?\n?)\s*<!-- END $block -->\s*?\n?/sm";
		preg_match_all($reg, $this->content, $m);

		if (!isset($m[1][0])) return false;
		$blockContent = $m[1][0];
		$t = explode('<!-- ELSE ' . $block . ' -->', $blockContent);
		$blockContent = rtrim($t[0], " \t");

		$this->blockCached[$block] = $blockContent;
		unset($this->blockParsed[$block]);
		return true;
	}

	/**
	 * @param array $match
	 * @return string
	 */
	private function getBlockContent($match) {
		$block = $match[1];
		if (isset($this->blockParsed[$block])) {
			$out = $this->blockParsed[$block];
			unset($this->blockParsed[$block]);
		} else {
			$t = explode('<!-- ELSE ' . $block . ' -->', $match[2]);
			$out = isset($t[1]) ? ltrim(rtrim($t[1], " \t"), "\r\n") : '';
		}
		return $out;
	}

}