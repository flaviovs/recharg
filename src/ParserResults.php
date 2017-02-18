<?php

namespace Recharg;

class ParserResults extends \ArrayObject {
	protected $commands = [];
	protected $operands = [];

	public function pushCommand($name) {
		$this->commands[] = $name;
	}

	public function pushOperand($op) {
		$this->operands[] = $op;
	}

	public function getCommands() {
		return $this->commands;
	}

	public function getOperands() {
		return $this->operands;
	}

	public function getArguments() {
		return $this->getArrayCopy();
	}

	public function mergeOperands(array $operands) {
		$this->operands = array_merge($this->operands, $operands);
	}

	public function reset() {
		$this->commands = [];
		$this->operands = [];
		$this->exchangeArray([]);
	}
}
