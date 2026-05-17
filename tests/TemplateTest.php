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

	public function testSetVarWithSpecialChar() {
		$this->template->setFile(__DIR__ . '/templates/template.tpl');
		$this->template->setVar('NAME', '\0 $1 \2');
		$this->assertEquals(
			'hello \0 $1 \2 world',
			$this->template->getRender()
		);
	}

	public function testSetVarAppend() {
		$this->template->setFile(__DIR__ . '/templates/template.tpl');
		$this->template->setVar('NAME', 'foo');
		$this->template->setVar('NAME', 'bar', true);
		$this->template->setVar('NAME', 'baz', true);
		$this->assertEquals(
			'hello foobarbaz world',
			$this->template->getRender()
		);
	}

	public function testSetBlock() {
		$data = ['foo', 'bar', 'baz'];
		$this->template->setFile(__DIR__ . '/templates/template_block.tpl');
		$this->template->setVar('VAR', 'hello');
		foreach ($data as $var) {
			$this->template->setVar('VAR', $var);
			$this->template->setBlock('BLOCK');
		}
		$this->assertEquals(
			'bazfoobarbaz',
			$this->template->getRender()
		);
	}

	public function testSetBlockWithVars() {
		$data = ['<foo>', '<bar>', '<baz>'];
		$this->template->setFile(__DIR__ . '/templates/template_block.tpl');
		$this->template->setVar('VAR', 'hello');
		foreach ($data as $var) {
			$this->template->setBlock('BLOCK', ['VAR' => $var]);
		}
		$this->assertEquals(
			'hello<foo><bar><baz>',
			$this->template->getRender()
		);
	}

	public function testSetBlockWithSpecialVars() {
		$data = ['$1', '$2', '$3', '$foo', '$bar', '$baz', '\1', '\2', '\3', '\foo', '\bar', '\baz'];
		$this->template->setFile(__DIR__ . '/templates/template_block.tpl');
		$this->template->setVar('VAR', 'hello');
		foreach ($data as $var) {
			$this->template->setBlock('BLOCK', ['VAR' => $var]);
		}
		$this->assertEquals(
			'hello$1$2$3$foo$bar$baz\1\2\3\foo\bar\baz',
			$this->template->getRender()
		);
	}

	public function testDefaultBlock() {
		$this->template->setFile(__DIR__ . '/templates/template_block.tpl');
		$this->assertEquals(
			'{VAR}Default block',
			$this->template->getRender()
		);
	}

	public function testNestedBlock() {
		$this->template->setFile(__DIR__ . '/templates/template_nested_block.tpl');

		foreach (['One', 'Two', 'Three'] as $block) {
			foreach (['A', 'B', 'C'] as $var) {
				$this->template->setVar('VAR_TWO', $var);
				$this->template->setBlock('BLOCK_TWO');
			}
			$this->template->setVar('VAR_ONE', $block);
			$this->template->setBlock('BLOCK_ONE');
		}

		$this->assertEquals(
			'*One:-A-B-C*Two:-A-B-C*Three:-A-B-C',
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
		$this->template->setContent('{SLOT NAME/}');
		$this->assertEquals(
			'{SLOT NAME/}',
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
		$this->template->setContent('{SLOT_NAME/Hello world}');
		$this->assertEquals(
			'Hello world',
			$this->template->getRender()
		);
	}

	public function testQuotedSlot() {
		$this->template->setContent('{"hello world"}');
		$this->assertEquals(
			'hello world',
			$this->template->getRender()
		);
		$this->template->setContent('{"hello world"}');
		$this->template->setVar('hello world', 'bonjour monde');
		$this->assertEquals(
			'bonjour monde',
			$this->template->getRender()
		);
		$this->template->setContent("{'foo bar baz'}");
		$this->assertEquals(
			'foo bar baz',
			$this->template->getRender()
		);
	}

	public function testSetVarNull() {
		$this->template->setContent('hello{SLOT}');
		$this->template->setVar('SLOT', null);
		$this->assertEquals(
			'hello',
			$this->template->getRender()
		);
	}

	public function testSlotWithQuestionMark() {
		$this->template->setContent('{"hello?"}');
		$this->assertEquals(
			'hello?',
			$this->template->getRender()
		);

		$this->template->setContent('{"hello?"}');
		$this->template->setVar('hello?', 'salut ?');
		$this->assertEquals(
			'salut ?',
			$this->template->getRender()
		);

		$template = new Template();
		$template->setContent('<!-- BEGIN BLOCK -->{"hello?"}<!-- END BLOCK -->');
		$template->setBlock('BLOCK');
		$this->assertEquals(
			'hello?',
			$template->getRender()
		);
	}

	public function testSlotWithMark() {
		$this->template->setContent('{"hello!"}');
		$this->assertEquals(
			'hello!',
			$this->template->getRender()
		);

		$this->template->setContent('{"hello."}');
		$this->assertEquals(
			'hello.',
			$this->template->getRender()
		);

		$this->template->setContent('{"hello;"}');
		$this->assertEquals(
			'hello;',
			$this->template->getRender()
		);

		$this->template->setContent('{"hello:"}');
		$this->assertEquals(
			'hello:',
			$this->template->getRender()
		);

		$this->template->setContent('{"je t\'aime"}');
		$this->assertEquals(
			'je t\'aime',
			$this->template->getRender()
		);
	}

	public function testInvalidSlotFormat() {
		$this->template->setContent('{/foo}');
		$this->assertEquals(
			'{/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{ /foo}');
		$this->assertEquals(
			'{ /foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{	/foo}');
		$this->assertEquals(
			'{	/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{' . PHP_EOL . '// foo}');
		$this->assertEquals(
			'{' . PHP_EOL . '// foo}',
			$this->template->getRender()
		);

		$text = '{
			"status": "ok",
			"html": "<div class=\"container\">\n\tHello world\n<\/div>",
			"scss": "",
			"js": "if (true) {\r\n\t\/\/ foo\r\n}"
		}';
		$this->template->setContent($text);
		$this->assertEquals(
			$text,
			$this->template->getRender()
		);
	}

	public function testSlotDefaultValueInvalidFormats() {
		// Test that invalid slot names with defaults are NOT matched and remain unchanged

		// Only underscores - should NOT match
		$this->template->setContent('{_/foo}');
		$this->assertEquals(
			'{_/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{__/foo}');
		$this->assertEquals(
			'{__/foo}',
			$this->template->getRender()
		);

		// Only digits - should NOT match
		$this->template->setContent('{123/foo}');
		$this->assertEquals(
			'{123/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{1/foo}');
		$this->assertEquals(
			'{1/foo}',
			$this->template->getRender()
		);

		// Starts with digit - should NOT match
		$this->template->setContent('{1VAR/foo}');
		$this->assertEquals(
			'{1VAR/foo}',
			$this->template->getRender()
		);

		// Underscore followed only by digits - should NOT match
		$this->template->setContent('{_1/foo}');
		$this->assertEquals(
			'{_1/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{_12/foo}');
		$this->assertEquals(
			'{_12/foo}',
			$this->template->getRender()
		);

		// Dashes not allowed - should NOT match
		$this->template->setContent('{-/foo}');
		$this->assertEquals(
			'{-/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{A-B/foo}');
		$this->assertEquals(
			'{A-B/foo}',
			$this->template->getRender()
		);

		$this->template->setContent('{-SLOT/foo}');
		$this->assertEquals(
			'{-SLOT/foo}',
			$this->template->getRender()
		);
	}

	public function testSlotDefaultValueValidFormats() {
		// Test that valid slot names with defaults ARE matched and replaced

		// Single letter
		$this->template->setContent('{A/foo}');
		$this->assertEquals(
			'foo',
			$this->template->getRender()
		);

		// Letters with digits
		$this->template->setContent('{ABC123/bar}');
		$this->assertEquals(
			'bar',
			$this->template->getRender()
		);

		// Underscore prefix with letter
		$this->template->setContent('{_A/baz}');
		$this->assertEquals(
			'baz',
			$this->template->getRender()
		);

		// Underscore prefix with letter and digit
		$this->template->setContent('{_A1/test}');
		$this->assertEquals(
			'test',
			$this->template->getRender()
		);

		// Underscore in the middle
		$this->template->setContent('{SLOT_NAME/value}');
		$this->assertEquals(
			'value',
			$this->template->getRender()
		);

		// Multiple underscores and letters
		$this->template->setContent('{SLOT_NAME_123/default}');
		$this->assertEquals(
			'default',
			$this->template->getRender()
		);
	}

}