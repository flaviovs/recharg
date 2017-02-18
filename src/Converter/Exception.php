<?php

namespace Recharg\Converter;

class Exception extends \Recharg\Exception {
	protected $input;

	public function __construct($input,
	                            $message = "",
	                            $code = 0,
	                            Throwable $prev = NULL) {
		parent::__construct($message, $code, $prev);
		$this->input = $input;
	}
}
