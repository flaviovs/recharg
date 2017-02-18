<?php

namespace Recharg\Test;

use Recharg\CommandLine;
use Recharg\Option;

trait CommandLineMockBuilder {

	protected function buildMockOption($name, array $spec) {
		$option = $this->createMock(Option::class);

		$option
			->method('getName')
			->willReturn($name);

		$option
			->method('getMatches')
			->willReturn(array_map(function($e) {
						return strlen($e) == 1 ? "-$e" : "--$e";
					}, $spec['matches']));

		$option
			->method('convert')
			->will($this->returnArgument(0));

		$option
			->method('acceptsArguments')
			->willReturn(!empty($spec['accepts_arguments']));

		if (isset($spec['help'])) {
			$option
				->method('getHelp')
				->willReturn($spec['help']);
		}

		if (isset($spec['multiple'])) {
			switch ($spec['multiple']) {
			case 'STORE_LAST':
				$mh = Option::MULTIPLE_STORE_LAST;
				break;
			case 'COUNT':
				$mh = Option::MULTIPLE_COUNT;
				break;
			case 'APPEND':
				$mh = Option::MULTIPLE_APPEND;
				break;
			default:
				throw new \Exception("Invalid multiple handling value: $spec[multiple]");
			}
			$option
				->method('getMultipleHandling')
				->willReturn($mh);
		}

		if (isset($spec['default'])) {
			$option
				->method('getDefault')
				->willReturn($spec['default']);
			$option
				->method('hasDefault')
				->willReturn($spec['default'] !== NULL);
		} else {
			$option
				->method('hasDefault')
				->willReturn(FALSE);
		}

		if (isset($spec['value'])) {
			$option
				->method('getValue')
				->willReturn($spec['value']);
		} else {
			$option
				->method('getValue')
				->willReturn(TRUE);
		}

		$option
			->method('isRequired')
			->willReturn(!empty($spec['required']));

		return $option;
	}

	protected function buildMockCommandLine(array $spec) {
		$cmdline = $this->createMock(CommandLine::class);

		$options = [];
		if (isset($spec['options'])) {
			foreach ($spec['options'] as $name => $optionspec) {
				$options[] = $this->buildMockOption($name, $optionspec);
			}
		}
		$cmdline
			->method('getOptions')
			->willReturn($options);

		$cmdline
			->method('getOperands')
			->willReturn(isset($spec['operands']) ? $spec['operands'] : NULL);

		// Create mocks for commands. First, create the commands array.
		$commands = [];
		if (isset($spec['commands'])) {
			foreach ($spec['commands'] as $name => $command_spec) {
				$commands[$name] = $this->buildMockCommandLine($command_spec);
			}
		}

		// Mock CommandLine->getCommands()
		$cmdline
			->method('getCommands')
			->willReturn($commands);

		// Mock CommandLine->getCommand(). This is pretty much a dup of the
		// code in the CommandLine class.
		$cmdline
			->method('getCommand')
			->will($this->returnCallback(
				       function(array $commands) use ($cmdline) {
					       if (!$commands) {
						       return $cmdline;
					       }
					       $cmdlines = $cmdline->getCommands();
					       return $cmdlines[$commands[0]]->getCommand(array_splice($commands, 1));
				       }));

		// Mock description and footer
		$cmdline
			->method('getDescription')
			->willReturn(isset($spec['description']) ? $spec['description'] : NULL);
		$cmdline
			->method('getFooter')
			->willReturn(isset($spec['footer']) ? $spec['footer'] : NULL);

		return $cmdline;
	}
}
