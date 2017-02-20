<?php

namespace Recharg;

class HelpFormatter {

	const HELP_WIDTH = 76;
	const HELP_OPTIONS_WIDTH = 26;

	protected $label = 'Usage: ';

	public function setUsageLabel($label) {
		$this->label = $label;
		return $this;
	}

	public function getUsageLabel() {
		return $this->label;
	}

	/**
	 * Custom wordwrap with indenting support
	 *
	 * Wrap text at some specified column, optionally indenting subsequent
	 * lines. Pretty much like PHP's wordwrap(), except that the latter
	 * doesn't plays well with $break parameters larger than 1 character,
	 * which could be used to implement indenting.
	 *
	 * This function does its job doing some tricks with PHP's
	 * wordwrap(). It should probably be rewritten in the future to be more
	 * elegant and efficient.
	 *
	 * NB: the method signature is slightly different from PHP's wordwrap().
	 */
	protected function indentWrap($str, $width, $indent = 0) {
		if ($indent == 0) {
			// No indentation requested. wordwrap() is perfectly fine in
			// these cases.
			return wordwrap($str, $width);
		}
		// Wordwrap using full width, and split into lines/
		$lines = explode("\n", wordwrap($str, $width));
		// Get the first line, the one which we do NOT want to be indented.
		$line1 = array_shift($lines);
		if (!$lines) {
			// There's only one line. Nothing else to do.
			return $line1;
		}

		// This is our left padding margin.
		$padding = str_repeat(' ', $indent);

		// Now re-implode the lines after the first, and re-wrap then using
		// full width, minus the desired indent. Explode the result, and add
		// the padding using array_map(). Next, implode everything again,
		// concatenate to the first line, and we're done.
		return $line1
			. "\n"
			. implode("\n",
			          array_map(function($line) use ($padding) {
					          return $padding . $line;
				          },
				          explode("\n",
				                  wordwrap(implode(" ", $lines),
				                           $width - $indent))));
	}

	protected function wordwrap($text, $width, $break = "\n") {
		return wordwrap($text, $width, $break);
	}

	protected function compareOptions(Option $a, Option $b) {
		$opt_a = $a->getMatches()[0];
		$opt_b = $b->getMatches()[0];

		$opt_a = ($opt_a[1] == '-' ? substr($opt_a, 2) : substr($opt_a, 1));
		$opt_b = ($opt_b[1] == '-' ? substr($opt_b, 2) : substr($opt_b, 1));

		return strcasecmp($opt_a, $opt_b);
	}

	protected function getOptionsSorted(CommandLine $cmd) {
		$options = $cmd->getOptions();
		@uasort($options, [$this, 'compareOptions']);
		return $options;
	}

	protected function makeUsage(CommandLine $cmdline) {
		$short_required = '';
		$short_required_wargs = [];
		$short_optional = '';
		$short_optional_wargs = [];
		$long_optional = [];
		$long_required = [];

		foreach ($this->getOptionsSorted($cmdline) as $opt) {
			if ($opt->isHidden()) {
				continue;
			}

			// We only process the first match.
			$flag = $opt->getMatches()[0];

			$placeholder = $opt->getPlaceholder();

			if (strlen($flag) == 2) {
				// "-a" -- a short option
				if ($opt->acceptsArguments()) {
					if ($opt->isRequired()) {
						$short_required_wargs[] = "$flag $placeholder";
					} else {
						$short_optional_wargs[] = "[$flag $placeholder]";
					}
				} else {
					if ($opt->isRequired()) {
						$short_required .= $flag[1];
					} else {
						$short_optional .= $flag[1];
					}
				}
			} else {
				// A long option
				if ($opt->acceptsArguments()) {
					if ($opt->isRequired()) {
						if ($opt->hasDefault()) {
							$long_required[] = $flag . "[=$placeholder]";
						} else {
							$long_required[] = "$flag=$placeholder";
						}
					} else {
						if ($opt->hasDefault()) {
							$long_optional[] = "[$flag" . "[=$placeholder]]";
						} else {
							$long_optional[] = "[$flag=$placeholder]";
						}
					}
				} else {
					if ($opt->isRequired()) {
						$long_required[] = $flag;
					} else {
						$long_optional[] = "[$flag]";
					}
				}
			}
		}

		$summary = [];
		if ($short_required) {
			$summary[] = '-' . $short_required;
		}

		if ($short_required_wargs) {
			$summary[] = implode(' ', $short_required_wargs);
		}

		if ($long_required) {
			$summary[] = implode(' ', $long_required);
		}

		if ($short_optional) {
			$summary[] = "[-$short_optional]";
		}

		if ($short_optional_wargs) {
			$summary[] = implode(' ', $short_optional_wargs);
		}

		if ($long_optional) {
			$summary[] = implode(' ', $long_optional);
		}

		return implode(' ', $summary);
	}

	protected function getUsage(CommandLine $cmdline, $command) {
		$usage = $cmdline->getUsage();
		if ($usage) {
			return $this->label . $usage;
		}

		$usage = $this->label . $command;

		$cmdline_usage = $this->makeUsage($cmdline);
		if ($cmdline_usage) {
			$usage .= " $cmdline_usage";
		}

		$operands = $cmdline->getOperands();

		if (!$operands && $cmdline->getCommands()) {
			// This command line has commands, but no operand help, so
			// return a suitable operand usage.
			$operands = "COMMAND";
		}

		if ($operands) {
			if ($usage) {
				$usage .= ' ';
			}
			$usage .= $operands;
		}

		// Wrap the usage line, identing the lines based on full command
		// length (capped at 30, to avoid problems with long command
		// chains).
		return $this->indentWrap(
			$usage,
			static::HELP_WIDTH,
			min(strlen($command), 30) + strlen($this->label) + 3
		);
	}

	protected function formatDescription(CommandLine $cmd) {
		$descr = $cmd->getDescription();
		return ($descr
		        ? "\n" . $this->indentWrap($descr, static::HELP_WIDTH)
		        : '');
	}

	public function format(CommandLine $cmdline, array $commands) {
		$full_name = array_shift($commands);

		if ($commands) {
			$full_name .= ' ' . implode(' ', $commands);
			$cmd = $cmdline->getCommand($commands);
		} else {
			$cmd = $cmdline;
		}

		$help = $this->getUsage($cmd, $full_name);

		$help .= $this->formatDescription($cmd);

		$help_text_width = static::HELP_WIDTH - 4 - static::HELP_OPTIONS_WIDTH;

		$empty_options_column = str_repeat(' ', static::HELP_OPTIONS_WIDTH);

		$options_help = '';

		foreach ($this->getOptionsSorted($cmd) as $opt) {
			if ($opt->isHidden()) {
				continue;
			}
			$short = [];
			$long = [];
			$placeholder = $opt->getPlaceholder();
			foreach ($opt->getMatches() as $match) {
				if (strlen($match) == 2) {
					if ($opt->acceptsArguments()) {
						if ($opt->hasDefault()) {
							$match .= " [$placeholder]";
						} else {
							$match .= " $placeholder";
						}
					}
					$short[] = $match;
				} else {
					if ($opt->acceptsArguments()) {
						if ($opt->hasDefault()) {
							$match .= "[=$placeholder]";
						} else {
							$match .= "=$placeholder";
						}
					}
					$long[] = $match;
				}
			}
			$lines = explode("\n",
			                 $this->indentWrap(implode(', ',
			                                         array_merge($short,
			                                                     $long)),
			                                 static::HELP_OPTIONS_WIDTH,
			                                 4));
			$options_help .= "\n";

			$nr_lines = count($lines) - 1;
			for ($i = 0; $i < $nr_lines; $i++) {
				$options_help .= "  $lines[$i]\n";
			}

			$options_help .= "  " . str_pad($lines[$i],
			                                static::HELP_OPTIONS_WIDTH);
			// The line in the option still can be so long that it cannot be
			// wrapped (e.g., --verylongoptionwith=MANDATORY-ARGUMENTS). If
			// that's the case, we force a line break so that everything
			// aligns properly.
			if (strlen($lines[$i]) > static::HELP_OPTIONS_WIDTH) {
				$options_help .= "\n$empty_options_column  ";
			}

			$options_help .= $this->wordwrap(
				($opt->getHelp() ?: "(No help text for this option.)"),
				$help_text_width,
				"\n$empty_options_column    ");
		}

		if ($options_help) {
			$help .= "\n\nOptions and arguments:\n";
			$help .= $options_help;
		}

		$cmdlines = $cmd->getCommands();
		if ($cmdlines) {
			$help .= "\n\nValid \"" . $full_name . "\" commands:\n";
			foreach ($cmdlines as $name => $os) {
				$help .= "\n  " . str_pad($name,
				                          static::HELP_OPTIONS_WIDTH)
					. $this->wordwrap(($os->getSummary()
					                   ?: '(No help text for this command.)'),
					                  $help_text_width,
					                  "\n$empty_options_column    ");
			}
		}

		$tmp = $cmd->getFooter();
		if ($tmp) {
			$help .= "\n\n" . $this->wordwrap($tmp, static::HELP_WIDTH);
		}

		return $help;
	}
}
