<?php

namespace Recharg\Test;

abstract class ConverterTestCase extends \PHPUnit\Framework\TestCase {
	abstract protected function getConverter();
	abstract protected function validDataProvider();
	abstract protected function invalidDataProvider();

	/**
	 * @dataProvider validDataProvider
	 */
	function testValidData($data, $expected) {
		$c = $this->getConverter();
		$this->assertSame($expected, $c->convert($data));
	}

	/**
	 * @dataProvider invalidDataProvider
	 */
	function testInvalidData($data) {
		$c = $this->getConverter();
		$this->expectException('Recharg\Converter\Exception');
		$c->convert($data);
	}

}
