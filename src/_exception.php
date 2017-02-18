<?php

namespace Recharg;

class Exception extends \Exception {}
class ErrorException extends Exception {}

abstract class ParserException extends Exception {
	protected $input;
	protected $commands = [];

	public function __construct($input,
	                            array $commands,
	                            $message = "",
	                            $code = 0,
	                            Throwable $prev = NULL) {
		parent::__construct($message, $code, $prev);
		$this->commands = $commands;
		$this->input = $input;
	}

	public function getInput() {
		return $this->input;
	}

	public function getCommands() {
		return $this->commands;
	}
}

class InvalidOptionException extends ParserException {}
class InvalidArgumentException extends ParserException {}
class InvalidCommandException extends ParserException {}
