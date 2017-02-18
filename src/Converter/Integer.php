<?php

namespace Recharg\Converter;

class Integer extends Numeric {

	public function convert($data) {
		$intval = (int)parent::convert($data);
		if ($intval != $data) {
			throw new Exception($data, "Not a valid integer: $data");
		}
		return $intval;
	}
}
