<?php

namespace Recharg\Converter;

class Boolean implements \Recharg\Converter {
	const TRUE_ARG = ['true', 'yes', 'on'];
	const FALSE_ARG = ['false', 'no', 'off'];

	public function convert($data) {
		$res = ($data === '' ? NULL : filter_var($data,
		                                         FILTER_VALIDATE_BOOLEAN,
		                                         FILTER_NULL_ON_FAILURE));
		if ($res === NULL) {
			throw new Exception($data, "Not a valid true/false value: $data");
		}

		return $res;
	}
}
