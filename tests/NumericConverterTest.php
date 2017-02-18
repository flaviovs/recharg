<?php

use Recharg\Converter\Numeric;

class NumericConverterTest extends Recharg\Test\ConverterTestCase {

	public function getConverter() {
		return new Numeric();
	}

	public function validDataProvider() {
		return [
			['0', 0.0],
			['1', 1.0],
			['+1', 1.0],
			['-1', -1.0],
			['1.1', 1.1],
			['1e1', 1e1],
		];
	}

	public function invalidDataProvider() {
		return [
			[''],
			['a'],
		];
	}


	public function testBetweenMinMax() {
		$c = new Numeric(1, 3);

		$this->assertEquals(1, $c->convert("1"));
		$this->assertEquals(2, $c->convert("2"));
		$this->assertEquals(3, $c->convert("3"));

	}

	public function testGetMin() {
		$c = new Numeric(1234);
		$this->assertEquals(1234, $c->getMin());
		$this->assertNull($c->getMax());
	}

	public function testGetMax() {
		$c = new Numeric(NULL, 1234);
		$this->assertNull($c->getMin());
		$this->assertEquals(1234, $c->getMax());
	}

	public function testLessThanMin() {
		$c = new Numeric(0);
		$this->assertEquals(0, $c->convert("0"));
		$this->expectException('Recharg\Converter\Exception');
		$c->convert("-1");
	}

	public function testGreaterThanMax() {
		$c = new Numeric(NULL, 0);
		$this->assertEquals(0, $c->convert("0"));
		$this->expectException('Recharg\Converter\Exception');
		$c->convert("1");
	}
}
