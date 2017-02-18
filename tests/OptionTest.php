<?php

use Recharg\Option;

class OptionTest extends PHPUnit\Framework\TestCase {

	public function testGetMultipleHandling() {
		$opt = new Option('verbose', ['v', 'verbose'], FALSE);
		$opt->setMultipleHandling(Option::MULTIPLE_COUNT);
		$this->assertEquals(Option::MULTIPLE_COUNT,
		                    $opt->getMultipleHandling());
	}

	public function testSetMultipleHandlingCountWithArgThrowsException() {
		$opt = new Option('verbose', ['v', 'verbose'], TRUE);
		$this->expectException('Recharg\\ErrorException');
		$opt->setMultipleHandling(Option::MULTIPLE_COUNT);
	}

	public function testSetMultipleHandlingAppendWithoutArgThrowsException() {
		$opt = new Option('verbose', ['v', 'verbose'], FALSE);
		$this->expectException('Recharg\\ErrorException');
		$opt->setMultipleHandling(Option::MULTIPLE_APPEND);
	}

	public function testConstructorDefaultAcceptsArgumentsToFalse() {
		$opt = new Option('verbose', ['v', 'verbose']);
		$this->assertFalse($opt->acceptsArguments());
	}

	public function testConstructorAcceptsArgumentsHandling() {
		$opt = new Option('verbose', ['v', 'verbose'], TRUE);
		$this->assertTrue($opt->acceptsArguments());
	}

	public function testGetAcceptsArguments() {
		$opt = new Option('verbose');
		$opt->setAcceptsArguments(TRUE);
		$this->assertTrue($opt->acceptsArguments());
	}

	public function testSetAcceptsArgumentsForMultipleCountFails() {
		$opt = new Option('verbose');
		$opt->setMultipleHandling(Option::MULTIPLE_COUNT);
		$this->expectException('Recharg\ErrorException');
		$opt->setAcceptsArguments(TRUE);
	}

	public function testSetAcceptsArgumentsForMultipleAppendFails() {
		$opt = new Option('verbose', ['v'], TRUE);
		$opt->setMultipleHandling(Option::MULTIPLE_APPEND);
		$this->expectException('Recharg\ErrorException');
		$opt->setAcceptsArguments(FALSE);
	}


	public function testGetConverter() {
		$opt = new Option('verbose', ['v'], TRUE);

		$converter = $this->createMock(\Recharg\Converter::class);
		$opt->setConverter($converter);
		$this->assertSame($converter, $opt->getConverter());
	}

	public function testConvert() {
		$opt = new Option('verbose', ['v'], TRUE);

		$converter = $this->createMock(\Recharg\Converter::class);
		$converter
			->method('convert')
			->willReturn('bar');

		$opt->setConverter($converter);
		$this->assertEquals('bar', $opt->convert('foo'));
	}

	public function testGetPlaceholder() {
		$opt = new Option('foobar');

		$this->assertEquals('FOOBAR', $opt->getPlaceholder());

		$opt->setPlaceholder('leemoo');
		$this->assertEquals('leemoo', $opt->getPlaceholder());
	}
}
