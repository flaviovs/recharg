<?php

namespace Recharg;

class Parser extends CommandLineParser {

	public function __construct(CommandLine $cmdline, $progname = NULL) {
		parent::__construct($cmdline, $progname ?: $GLOBALS['argv'][0]);
	}

	public function parse(array $argv = NULL) {
		if ($argv === NULL) {
			$argv = array_slice($GLOBALS['argv'], 1);
		}
		return parent::parse($argv);
	}
}
