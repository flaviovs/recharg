<?php

use Recharg\Parser;
use Recharg\CommandLine;
use Recharg\ParserResults;
use Recharg\Option;

class ParserTest extends PHPUnit\Framework\TestCase {

	use Recharg\Test\CommandLineMockBuilder;

	protected $saved_argv;


	protected function assertResultsEquals(array $arguments,
	                                       array $operands,
	                                       array $commands,
	                                       ParserResults $res) {
		$this->assertSame($arguments, $res->getArguments(),
		                  "Arguments mismatch");
		$this->assertSame($operands, $res->getOperands(),
		                  "Operands mismatch");
		// We remove the first command because it is the name of the first
		// command passed to the parser, and we're not interested in it.
		$res_commands = $res->getCommands();
		array_shift($res_commands);
		$this->assertSame($commands, $res_commands, "Commands mismatch");
	}


	protected function load($file) {
		$spec = json_decode(preg_replace('#^\s*//.*\n#m',
		                                 '',
		                                 file_get_contents($file)),
		                    TRUE);
		if ($spec === NULL) {
			throw new Exception("$file: Failed to decode JSON: " . json_last_error_msg());
		}

		$cmdline = $this->buildMockCommandLine($spec['cmdline']);

		$tests = [];
		foreach ($spec['tests'] as $test) {
			$tests[] = [
				$cmdline,
				$test['argv'],
				isset($test['expects']) ? $test['expects'] : [],
			];
		};

		return $tests;
	}


	protected function loadTestData($path) {
		$data = [];
		$path_len = strlen(dirname($path)) + 1;
		foreach (glob("$path/*.json") as $file) {
			$tests = $this->load($file);
			for ($i = 0; $i < count($tests); $i++) {
				$data[substr($file, $path_len) . "#" . ($i + 1)] = $tests[$i];
			}
		}
		return $data;
	}


	public function validDataProvider() {
		return $this->loadTestData(__DIR__ . "/data/parser");
	}


	public function invalidDataProvider() {
		return $this->loadTestData(__DIR__ . "/data/invalid");
	}


	/**
	 * @dataProvider validDataProvider
	 */
	public function testValidOptions(CommandLine $cmdline,
	                                 array $argv,
	                                 array $expected) {
		$p = new Parser($cmdline);
		$res = $p->parse($argv);

		$this->assertResultsEquals($expected['arguments'],
		                           isset($expected['operands']) ? $expected['operands'] : [],
		                           isset($expected['commands']) ? $expected['commands'] : [],
		                           $res);
	}


	/**
	 * @dataProvider invalidDataProvider
	 */
	public function testInvalidOptions(CommandLine $cmdline,
	                                   array $argv,
	                                   array $expected) {
		$p = new Parser($cmdline);

		$this->expectException('Recharg\InvalidOptionException');
		$p->parse($argv);
	}


	public function testDefaultValue() {
		$os = new CommandLine();
		$opt = new Option('foo');
		$opt->setDefault('bar');
		$os->addOption($opt);

		$p = new Parser($os);

		$this->assertResultsEquals(['foo' => 'bar'],
								   [],
								   [],
								   $p->parse([]));
	}

	public function testMultipleHandlingDefault() {
		$os = new CommandLine();
		$opt = new Option('verbose', ['v', 'verbose'], TRUE);
		$os->addOption($opt);
		$p = new Parser($os);
		// Default should be Option::MULTIPLE_STORE_LAST
		$this->assertResultsEquals(['verbose' => 'b'],
		                           [], [],
		                           $p->parse(['-v', 'a', '-v', 'b']));
	}

	public function testMultipleHandlingCount() {
		$os = new CommandLine();
		$opt = new Option('verbose', ['v', 'verbose'], FALSE);
		$opt->setMultipleHandling(Option::MULTIPLE_COUNT);
		$os->addOption($opt);
		$p = new Parser($os);
		$this->assertResultsEquals(['verbose' => 4],
		                           ['a', 'b'], [],
		                           $p->parse(['-v', 'a', '-v', 'b', '-vv']));
	}

	public function testMultipleHandlingAppend() {
		$os = new CommandLine();
		$opt = new Option('verbose', ['v', 'verbose'], TRUE);
		$opt->setMultipleHandling(Option::MULTIPLE_APPEND);
		$os->addOption($opt);
		$p = new Parser($os);
		$this->assertResultsEquals(['verbose' => ['a', 'b', 'vc']],
		                           [], [],
		                           $p->parse(['-v', 'a', '-v', 'b', '-vvc']));
	}

	public function testSubcommand() {
		$os = new CommandLine();
		$os->addOption(new Option('help', ['help']));
		$os->addOption(new Option('version', ['version']));

		$cmd_foo = new CommandLine();
		$cmd_foo->addOption(new Option('optfoo'));
		$os->addCommand('foo', $cmd_foo);

		$cmd_bar = new CommandLine();
		$cmd_bar->addOption(new Option('optbar'));

		$cmd_bar_zee = new CommandLine();
		$cmd_bar_zee->addOption(new Option('optzee'));
		$cmd_bar->addCommand('zee', $cmd_bar_zee);

		$os->addCommand('bar', $cmd_bar);

		$p = new Parser($os);
		$this->assertResultsEquals([
			                           'help' => TRUE,
			                           'version' => NULL,
			                           'optbar' => NULL,
			                           'optzee' => TRUE,
		                           ],
		                           ['bar'],
		                           ['bar', 'zee'],
		                           $p->parse([
			                                     '--help',
			                                     'bar',
			                                     'zee',
			                                     '--optzee',
			                                     'bar',
		                                     ]));

		$ex = NULL;
		try {
			$p->parse(['bar', 'INVALIDCOMMAND']);
		} catch (Recharg\InvalidCommandException $ex) {
			$this->assertEquals('INVALIDCOMMAND', $ex->getInput());
			$this->assertEquals([
				                    $GLOBALS['argv'][0],
				                    'bar',
			                    ], $ex->getCommands());
		}

		if (!$ex) {
			$this->fail('InvalidCommandException not thrown');
		}
	}
}
