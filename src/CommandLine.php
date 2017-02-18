<?php

namespace Recharg;

class CommandLine {
	protected $options = [];
	protected $cmdlines = [];
	protected $usage;
	protected $summary;
	protected $description;
	protected $operands;
	protected $footer;
	protected $help_formatter;

	public function getOptions() {
		return $this->options;
	}

	public function getCommands() {
		return $this->cmdlines;
	}

	public function addOption(Option $opt) {
		$this->options[] = $opt;
		return $this;
	}

	public function addCommand($name, CommandLine $os) {
		if (isset($this->cmdlines[$name])) {
			throw new ErrorException("A command named \"$name\" already exists in \"$this->name\"");
		}
		$this->cmdlines[$name] = $os;
		return $this;
	}

	public function setUsage($usage) {
		$this->usage = $usage;
		return $this;
	}

	public function getUsage() {
		return $this->usage;
	}

	public function setSummary($summary) {
		$this->summary = $summary;
		return $this;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setFooter($footer) {
		$this->footer = $footer;
		return $this;
	}

	public function getFooter() {
		return $this->footer;
	}

	public function setOperands($op) {
		$this->operands = $op;
		return $this;
	}

	public function getOperands() {
		return $this->operands;
	}

	public function setHelpFormatter(HelpFormatter $hf) {
		$this->help_formatter = $hf;
	}


	public function getHelpFormatter() {
		return $this->help_formatter;
	}


	public function getHelp(array $commands = []) {
		$hf = $this->help_formatter ?: new HelpFormatter();
		return $hf->format($this, $commands);
	}


	/**
	 * Transverse the CommandLine tree to find a command node by following a
	 * command chain.
	 */
	public function getCommand(array $commands = []) {
		if (!$commands) {
			return $this;
		}
		return $this->cmdlines[$commands[0]]->getCommand(array_splice($commands, 1));
	}

}
