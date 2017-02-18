<?php

namespace Recharg;

class CommandLineParser {

	const WAIT_ANY = 0;
	const WAIT_ARGUMENT = 1;
	const WAIT_OPERAND = 2;

	protected $name;
	protected $option; // Current option
	protected $options = [];
	protected $commands = [];
	protected $results;
	protected $lexer;

	protected $state = self::WAIT_ANY;

	public function __construct(CommandLine $cmdline, $name) {
		$this->options = $cmdline->getOptions();
		$this->commands = $cmdline->getCommands();
		$this->name = $name;

		$this->reset();
	}


	public function reset() {
		$this->results = new ParserResults();
		$this->results->pushCommand($this->name);

		foreach ($this->options as $opt) {
			$this->results[$opt->getName()] = $opt->convert($opt->getDefault());
		}
	}


	public function getName() {
		return $this->name;
	}


	protected function findOption($input) {
		$last_matched = NULL;
		foreach ($this->options as $option) {
			if (in_array($input, $option->getMatches())) {
				if ($last_matched && $option !== $last_matched) {
					throw new InvalidOptionException($input,
					                                 $this->results->getCommands(),
					                                 "Ambiguous option: $input");
				}
				$last_matched = $option;
			}
		}
		if (!$last_matched) {
			throw new InvalidOptionException($input,
			                                 $this->results->getCommands(),
			                                 "Invalid option: $input");
		}
		return $last_matched;
	}


	protected function setResult(Option $option, $value) {
		switch ($option->getMultipleHandling()) {
		case Option::MULTIPLE_STORE_LAST:
			$this->results[$option->getName()] = $option->convert($value);
			break;
		case Option::MULTIPLE_COUNT:
			$this->results[$option->getName()]++;
			break;
		case Option::MULTIPLE_APPEND:
			$this->results[$option->getName()][] = $option->convert($value);
			break;
		default:
			throw new \RuntimeError("Unknown multiple handling value "
			                        . $option->getMultipleHandling());
		}
	}


	protected function processShortOption($input) {
		if (strlen($input) > 2) {
			// We have something like "-abc". Let's split the option and
			// handle the possible argument later.
			$argument = substr($input, 2);
			$input = substr($input, 0, 2);
		} else {
			$argument = NULL;
		}

		$option = $this->findOption($input);

		if ($argument !== NULL) {
			// An "-abc" option. If the option doesn't accept argument, we
			// synthesize a SHORT_OPTION token for the argument, and feed
			// it back to the FSM.
			if ($option->acceptsArguments()) {
				$this->setResult($option, $argument);
				$this->state = self::WAIT_ANY;
			} else {
				$this->setResult($option, $option->getValue());
				$this->state = self::WAIT_ANY;
				$this->handleToken(new Token(Token::SHORT_OPTION,
				                             "-$argument"));
			}
		} else {
			// That was the simple "-a". Now if the option requires
			// argument, we change to the appropriate state, otherwise we
			// can just set the option user-specified value.
			if ($option->acceptsArguments()) {
				$this->option = $option;
				$this->state = self::WAIT_ARGUMENT;
			} else {
				$this->setResult($option, $option->getValue());
				$this->state = self::WAIT_ANY;
			}
		}
	}


	protected function processLongOption($input) {
		$eq_pos = strpos($input, '=');
		if ($eq_pos !== FALSE) {
			$argument = substr($input, $eq_pos + 1);
			$input = substr($input, 0, $eq_pos);
		} else {
			$argument = NULL;
		}

		$option = $this->findOption($input);

		if ($argument !== NULL) {
			if (!$option->acceptsArguments()) {
				throw new InvalidArgumentException(
					$input,
					$this->results->getCommands(),
					"Option does not accept arguments: $input"
				);
			}
			$this->setResult($option, $argument);
			$this->state = self::WAIT_ANY;
		} else {
			if ($option->acceptsArguments()) {
				$this->option = $option;
				$this->state = self::WAIT_ARGUMENT;
			} else {
				$this->setResult($option, $option->getValue());
				$this->state = self::WAIT_ANY;
			}
		}
	}


	protected function handleAny(Token $tok) {
		switch ($tok->getType()) {
		case Token::SHORT_OPTION:
			$this->processShortOption($tok->getContent());
			break;
		case Token::LONG_OPTION:
			$this->processLongOption($tok->getContent());
			break;
		case Token::WORD:
			$this->handleOperand($tok);
			break;
		case Token::EOO:
			$this->state = self::WAIT_OPERAND;
			break;
		default:
			throw new \RuntimeException('Cannot handle token type '
			                            . $tok->getType());
		}
	}


	protected function handleArgument(Token $tok) {
		$this->setResult($this->option, $tok->getContent());
		$this->state = self::WAIT_ANY;
		$this->option = NULL; // Just in case
	}


	protected function processCommand($command) {
		if (!isset($this->commands[$command])) {
			throw new InvalidCommandException($command,
			                                  $this->results->getCommands(),
			                                  "Invalid command: $command");
		}

		$parser = new CommandLineParser($this->commands[$command],
		                                $command);
		try {
			$results = $parser->parseTokens($this->lexer);
		} catch (ParserException $ex) {
			// Rethrow the exception, now with a command list that includes
			// this command.
			$class = get_class($ex);
			throw new $class($ex->getInput(),
			                 array_merge($this->results->getCommands(),
			                             $ex->getCommands()),
			                 $ex->getMessage());
		}
		foreach ($results->getCommands() as $cmdx) {
			$this->results->pushCommand($cmdx);
		}
		$this->results->mergeOperands($results->getOperands());
		foreach ($results->getArguments() as $name => $arg) {
			$this->results[$name] = $arg;
		}
	}


	protected function handleOperand(Token $tok) {
		if ($this->commands) {
			$this->processCommand($tok->getContent());
		} else {
			$this->results->pushOperand($tok->getContent());
		}
	}


	protected function handleToken(Token $tok) {
		switch ($this->state) {
		case self::WAIT_ANY:
			$this->handleAny($tok);
			break;

		case self::WAIT_ARGUMENT:
			$this->handleArgument($tok);
			break;

		case self::WAIT_OPERAND:
			$this->handleOperand($tok);
			break;
		}
	}


	protected function finish() {
		if ($this->state == self::WAIT_ARGUMENT) {
			$this->setResult($this->option, $this->option->getValue());
		}
	}


	public function parseTokens(Lexer $lexer) {
		$this->lexer = $lexer;
		while ($token = $this->lexer->getNext()) {
			$this->handleToken($token);
		}
		$this->finish();
		return $this->results;
	}


	public function parse(array $argv) {
		$this->reset();
		return $this->parseTokens(new Lexer($argv));
	}
}
