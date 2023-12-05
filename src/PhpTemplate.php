<?php
namespace Sy\Template;

class PhpTemplate implements ITemplate {

	private $content;

	private $file;

	private $vars;

	private $blocks;

	public function __construct() {
		$this->content = '';
		$this->file = '';
		$this->vars = array();
		$this->blocks = array();
	}

	public function setContent($content) {
		$this->content = $content;
		$this->file = '';
	}

	public function setFile($file) {
		if (!file_exists($file)) throw new TemplateFileNotFoundException("Template file note found: $file");
		$this->file = $file;
		$this->content = '';
	}

	public function setVar($var, $value, $append = false) {
		if ($append and isset($this->vars[$var])) {
			$this->vars[$var] .= $value;
		} else {
			$this->vars[$var] = $value;
		}
	}

	public function setBlock($block, $vars = array()) {
		$this->blocks[$block][] = empty($vars) ? $this->vars : $vars;
	}

	public function _($var) {
		return isset($this->vars[$var]) ? $this->vars[$var] : $var;
	}

	public function getRender() {
		if (!empty($this->content)) {
			$file = tempnam(sys_get_temp_dir(), 'sytpl');
			file_put_contents($file, $this->content);
			$this->file = $file;
		}
		if (empty($this->file)) return '';

		extract($this->blocks);
		extract($this->vars);

		$this->blocks = array();
		$this->vars = array();

		ob_start();
		include $this->file;
		$content = ob_get_contents();
		ob_end_clean();

		if (!empty($this->content)) {
			unlink($this->file);
		}

		return $content;
	}

}