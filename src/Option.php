<?php

namespace Recharg;

class Option {
	const MULTIPLE_STORE_LAST = 0;
	const MULTIPLE_COUNT = 1;
	const MULTIPLE_APPEND = 2;

	protected $name;
	protected $matches;
	protected $arguments;
	protected $count;
	protected $help;
	protected $default;
	protected $value = TRUE;
	protected $action;
	protected $type;
	protected $options;
	protected $placeholder;
	protected $converter;
	protected $required = FALSE;
	protected $multiple_handling = self::MULTIPLE_STORE_LAST;

	public function __construct($name, array $matches = NULL, $arguments = FALSE) {
		if (!$matches) {
			$matches = [$name];
		}
		$this->setName($name);
		$this->setMatches($matches);
		$this->setAcceptsArguments($arguments);
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	public function getName() {
		return $this->name;
	}

	public function setHelp($help) {
		$this->help = $help;
		return $this;
	}

	public function getHelp() {
		return $this->help;
	}

	public function setMatches(array $matches) {
		foreach ($matches as $match) {
			$this->addMatch($match);
		}
		return $this;
	}

	public function getMatches() {
		return $this->matches;
	}

	public function setAcceptsArguments($arguments) {
		if ($arguments) {
			if ($this->multiple_handling == self::MULTIPLE_COUNT) {
				throw new ErrorException('Cannot accept arguments for an option with multiple occurrences counting');
			}
		} else {
			if ($this->multiple_handling == self::MULTIPLE_APPEND) {
				throw new ErrorException('Cannot disable arguments for options with append multiple handling');
			}
		}

		$this->arguments = $arguments;
	}

	public function setMultipleHandling($mh) {
		switch ($mh) {
		case self::MULTIPLE_COUNT:
			if ($this->acceptsArguments()) {
				throw new ErrorException('Cannot count multiple occurrences of an option that accepts arguments');
			}
			break;
		case self::MULTIPLE_APPEND:
			if (!$this->acceptsArguments()) {
				throw new ErrorException('Append handling can be set only in options that accept arguments');
			}
			break;
		}

		$this->multiple_handling = $mh;
	}

	public function getMultipleHandling() {
		return $this->multiple_handling;
	}

	protected function addMatch($match) {
		if (strlen($match) == 1) {
			$match = "-$match";
		} else {
			$match = "--$match";
		}
		$this->matches[] = $match;
		return $this;
	}

	public function setDefault($default) {
		$this->default = $default;
		return $this;
	}

	public function getDefault() {
		switch ($this->multiple_handling) {
		case self::MULTIPLE_STORE_LAST:
			$def = $this->default;
			break;
		case self::MULTIPLE_APPEND:
			$def = [];
			break;
		case self::MULTIPLE_COUNT:
			$def = 0;
			break;
		}
		return $def;
	}

	public function hasDefault() {
		return $this->default !== NULL;
	}

	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	public function getValue() {
		return $this->value;
	}

	public function setConverter(Converter $converter) {
		$this->converter = $converter;
		return $this;
	}

	public function getConverter() {
		return $this->converter;
	}

	public function setRequired($required) {
		$this->required = $required;
		return $this;
	}

	public function setPlaceholder($placeholder) {
		$this->placeholder = $placeholder;
		return $this;
	}

	public function getPlaceholder() {
		return $this->placeholder ?: strtoupper($this->name);
	}

	public function isRequired() {
		return $this->required;
	}

	public function acceptsArguments() {
		return $this->arguments;
	}

	public function convert($data) {
		return $this->converter ? $this->converter->convert($data) : $data;
	}
}
