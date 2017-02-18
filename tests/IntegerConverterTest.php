<?php

use Recharg\Converter\Integer;

class IntegerConverterTest extends Recharg\Test\ConverterTestCase {

	public function getConverter() {
		return new Integer();
	}

	public function validDataProvider() {
		return [
			['0', 0],
			['1', 1],
			['+1', 1],
			['-1', -1],
			['1e2', 100]
		];
	}

	public function invalidDataProvider() {
		return [
			[''],
			['1.1'],
			['a'],
		];
	}
}
