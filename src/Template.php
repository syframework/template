<?php
namespace Sy\Template;

class Template implements ITemplate {

	private $content;

	private $vars;

	private $blockParsed;

	private $blockCached;

	public function __construct() {
		$this->content = '';
		$this->vars = array();
		$this->blockCached = array();
		$this->blockParsed = array();
	}

	public function setContent($content) {
		$this->content = preg_replace('/{\'([^\t\r\n\'\({}[":,]+)\'}/', '{"$1"}', $content);
	}

	public function setFile($file) {
		if (!file_exists($file)) throw new TemplateFileNotFoundException("Template file note found: $file");
		$this->setContent(file_get_contents($file));
	}

	public function setVar($var, $value, $append = false) {
		$this->vars[$var] = ($append and isset($this->vars[$var])) ? $this->vars[$var] . $value : $value;
	}

	public function setBlock($block, $vars = array()) {
		if (!$this->loadBlock($block)) return;

		$data = $this->blockCached[$block];
		if (strpos($data, '<!-- BEGIN') !== false) {
			$reg = "/[ \t]*<!-- BEGIN ([a-zA-Z0-9\._]*) -->(\s*?\n?\s*.*?\n?\s*)<!-- END \\1 -->\s*?\n?/sm";
			$data = preg_replace_callback($reg, array($this, 'getBlockContent'), $data);
		}

		$vars = empty($vars) ? $this->vars : $vars;
		$varkeys = array_keys($vars);
		$varvals = array_values($vars);
		$search = array_map(function($v) {return '/(?:{' . $v . '(?:\/[^{}\r\n]*)*})|(?:{"' . $v . '"})/';}, $varkeys);
		$res = preg_replace($search, $varvals, $data);
		$res = preg_replace('/{[^\t\r\n\'\({}[":,\/]+\/([^{}\r\n]*)}/', '$1', $res);

		$this->blockParsed[$block] = (isset($this->blockParsed[$block]) ? $this->blockParsed[$block] : '') . $res;
	}

	public function getRender() {
		if (strpos($this->content, '<!-- BEGIN') !== false) {
			$reg = "/[ \t]*<!-- BEGIN ([a-zA-Z0-9\._]*) -->(\s*?\n?\s*.*?\n?\s*)<!-- END \\1 -->\s*?\n?/sm";
			$this->content = preg_replace_callback($reg, array($this, 'getBlockContent'), $this->content);
		}

		$varkeys = array_keys($this->vars);
		$varvals = array_values($this->vars);
		$search = array_map(function($v) {return '/(?:{' . $v . '(?:\/[^{}\r\n]*)*})|(?:{"' . $v . '"})/';}, $varkeys);
		$res = preg_replace($search, $varvals, $this->content);
		$res = preg_replace('/{[^\t\r\n\'\({}[":,\/]+\/([^{}\r\n]*)}/', '$1', $res);
		$res = preg_replace('/{\"([^\t\r\n\'\({}[":,]+)\"}/', '$1', $res);
		return $res;
	}

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