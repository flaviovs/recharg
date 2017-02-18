<?php

use Recharg\Converter\Boolean;

class BooleanConverterTest extends Recharg\Test\ConverterTestCase {

	public function getConverter() {
		return new Boolean();
	}

	public function validDataProvider() {
		return [
			['0', FALSE],
			['1', TRUE],
			['false', FALSE],
			['true', TRUE],
			['no', FALSE],
			['yes', TRUE],
			['off', FALSE],
			['on', TRUE],
		];
	}

	public function invalidDataProvider() {
		return [
			[''],
			['a'],
			['yeah'],
			['aye'],
			['nay'],
		];
	}
}
