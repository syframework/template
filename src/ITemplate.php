<?php
namespace Sy\Template;

interface ITemplate {

	/**
	 * Sets a template content
	 *
	 * @param string $content
	 */
	public function setContent($content);

	/**
	 * Sets a template file
	 *
	 * @param string $file
	 * @throws TemplateFileNotFoundException
	 */
	public function setFile($file);

	/**
	 * Sets a value for a slot
	 *
	 * @param string $var
	 * @param string $value
	 * @param bool $append
	 */
	public function setVar($var, $value, $append = false);

	/**
	 * Sets a block
	 *
	 * @param string $block
	 */
	public function setBlock($block);

	/**
	 * Returns a template render
	 *
	 * @return string
	 */
	public function getRender();

}

class Exception extends \Exception {}
class TemplateFileNotFoundException extends Exception {}