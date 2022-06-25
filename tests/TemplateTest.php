<?php

use PHPUnit\Framework\TestCase;
use Sy\Template\Template;
use Sy\Template\TemplateFileNotFoundException;
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
			'hello {NAME} world',
			$this->template->getRender()
		);
	}

	public function testTemplateFileNotFound() {
		$this->expectException(TemplateFileNotFoundException::class);
		$this->template->setFile('nothing.tpl');
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

	public function testSlot() {
		$this->template->setFile(__DIR__ . '/templates/slot.tpl');
		$this->template->setVar('SLOT', 'hello');
		$this->assertEquals(
			'hello hello hello hello SLOT/foo {"SLOT} {SLOT"} {SLOT_A} {SLOT/{foo}}',
			$this->template->getRender()
		);
	}

	public function testDefaultSlot() {
		$this->template->setFile(__DIR__ . '/templates/slot.tpl');
		$this->assertEquals(
			'{SLOT}  SLOT foo SLOT/foo {"SLOT} {SLOT"} {SLOT_A} {SLOT/{foo}}',
			$this->template->getRender()
		);
	}

	public function testSlotNotCleared() {
		$this->template->setContent('{SLOT}');
		$this->assertEquals(
			'{SLOT}',
			$this->template->getRender()
		);
		$this->template->setContent('{SLOT_NAME}');
		$this->assertEquals(
			'{SLOT_NAME}',
			$this->template->getRender()
		);
		$this->template->setContent('{SLOT NAME}');
		$this->assertEquals(
			'{SLOT NAME}',
			$this->template->getRender()
		);
	}

	public function testSlotCleared() {
		$this->template->setContent('{SLOT/}');
		$this->assertEquals(
			'',
			$this->template->getRender()
		);
		$this->template->setContent('{SLOT_NAME/}');
		$this->assertEquals(
			'',
			$this->template->getRender()
		);
		$this->template->setContent('{SLOT NAME/}');
		$this->assertEquals(
			'',
			$this->template->getRender()
		);
	}

	public function testSlotDefaultValue() {
		$this->template->setContent('{SLOT/foo}');
		$this->assertEquals(
			'foo',
			$this->template->getRender()
		);
		$this->template->setContent('{SLOT/foo bar baz}');
		$this->assertEquals(
			'foo bar baz',
			$this->template->getRender()
		);
	}

	public function testQuotedSlot() {
		$this->template->setContent('{"hello world"}');
		$this->assertEquals(
			'hello world',
			$this->template->getRender()
		);
		$this->template->setContent("{'foo bar baz'}");
		$this->assertEquals(
			'foo bar baz',
			$this->template->getRender()
		);
	}

}