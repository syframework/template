<?php

use PHPUnit\Framework\TestCase;
use Sy\Template\PhpTemplate;
use Sy\Template\TemplateProvider;

class PhpTemplateTest extends TestCase {

	protected $template;

	protected function setUp() : void {
		$this->template = TemplateProvider::createTemplate('php');
	}

	public function testCreation() {
		$this->assertInstanceOf(
			PhpTemplate::class,
			$this->template
		);
	}

	public function testSetContent() {
		$this->template->setContent('hello world');
		$this->assertEquals(
			'hello world',
			$this->template->getRender()
		);
	}

	public function testSetFile() {
		$this->template->setFile(__DIR__ . '/templates/php_template.tpl');
		$this->assertEquals(
			'hello world',
			$this->template->getRender()
		);
	}

	public function testSetVar() {
		$this->template->setFile(__DIR__ . '/templates/php_template.tpl');
		$this->template->setVar('NAME', 'foo');
		$this->assertEquals(
			'hello foo',
			$this->template->getRender()
		);
	}

	public function testSetBlock() {
		$data = ['foo', 'bar', 'baz'];
		$this->template->setFile(__DIR__ . '/templates/php_template_block.tpl');
		foreach ($data as $var) {
			$this->template->setVar('VAR', $var);
			$this->template->setBlock('BLOCK');
		}
		$this->assertEquals(
			'foobarbaz',
			$this->template->getRender()
		);
	}

	public function testDefaultBlock() {
		$this->template->setFile(__DIR__ . '/templates/php_template_block.tpl');
		$this->assertEquals(
			'',
			$this->template->getRender()
		);
	}

}