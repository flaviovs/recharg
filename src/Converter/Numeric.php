<?php

namespace Recharg\Converter;

class Numeric implements \Recharg\Converter {

	protected $min;
	protected $max;

	public function __construct($min = NULL, $max = NULL) {
		$this->min = $min;
		$this->max = $max;
	}

	public function getMin() {
		return $this->min;
	}

	public function getMax() {
		return $this->max;
	}

	public function convert($data) {
		if (!is_numeric($data)) {
			throw new Exception($data, "Not a valid number: $data");
		}

		$data = filter_var($data, FILTER_VALIDATE_FLOAT);

		if ($this->min !== NULL && $data < $this->min) {
			throw new Exception($data, "Must not be less than $this->min");
		}

		if ($this->max !== NULL && $data > $this->max) {
			throw new Exception($data, "Must not be less than $this->min");
		}

		return $data;
	}
}
