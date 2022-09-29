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
	 * @param array $vars Associative array('SLOT_NAME' => 'Slot value').
	 *              Isolated vars to use for the block, use the template vars if empty
	 */
	public function setBlock($block, $vars = array());

	/**
	 * Returns a template render
	 *
	 * @return string
	 */
	public function getRender();

}

class Exception extends \Exception {}
class TemplateFileNotFoundException extends Exception {}