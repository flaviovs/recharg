<?php

namespace Recharg;

class Token {
	const SHORT_OPTION = 1;
	const LONG_OPTION = 2;
	const WORD = 3;
	const EOO = 4;

	protected $type;
	protected $content;

	public function __construct($type, $content = NULL) {
		$this->type = $type;
		$this->content = $content;
	}

	public function getType() {
		return $this->type;
	}

	public function getContent() {
		return $this->content;
	}
}
