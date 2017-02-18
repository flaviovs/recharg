<?php

namespace Recharg;

class Lexer {

	protected $argv;
	protected $argc;
	protected $current;

	public function __construct(array $argv) {
		$this->argv = $argv;
		$this->argc = count($argv);
		$this->rewind();
	}

	public function rewind() {
		$this->current = 0;
	}

	public function getNext() {
		if ($this->current == $this->argc) {
			return NULL;
		}

		$t = $this->argv[$this->current++];

		$len = strlen($t);
		if ($len == 0) {
			return new Token(Token::WORD, '');
		}

		if ($len == 1 || $t[0] !== '-') {
			return new Token(Token::WORD, $t);
		}

		if ($t === '--') {
			return new Token(Token::EOO);
		}

		if ($t[1] === '-') {
			return new Token(Token::LONG_OPTION, $t);
		}

		return new Token(Token::SHORT_OPTION, $t);
	}
}
