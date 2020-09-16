<?php

use PHPUnit\Framework\TestCase;
use Sy\Template\Template;
use Sy\Template\TemplateProvider;

class TemplateTest extends TestCase {

	protected $template;

	protected function setUp() : void {
		$this->template = TemplateProvider::createTemplate();
	}

	public function testCreation() {
		$this->assertInstanceOf(
			Template::class,
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
		$this->template->setFile(__DIR__ . '/templates/template.tpl');
		$this->assertEquals(
			'hello  world',
			$this->template->getRender()
		);
	}

	public function testSetVar() {
		$this->template->setFile(__DIR__ . '/templates/template.tpl');
		$this->template->setVar('NAME', 'foo');
		$this->assertEquals(
			'hello foo world',
			$this->template->getRender()
		);
	}

	public function testSetBlock() {
		$data = ['foo', 'bar', 'baz'];
		$this->template->setFile(__DIR__ . '/templates/template_block.tpl');
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
		$this->template->setFile(__DIR__ . '/templates/template_block.tpl');
		$this->assertEquals(
			'Default block',
			$this->template->getRender()
		);
	}

}