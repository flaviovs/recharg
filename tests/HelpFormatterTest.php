<?php

use Recharg\HelpFormatter;

class HelpFormatterTestTest extends \PHPUnit\Framework\TestCase {

	use Recharg\Test\CommandLineMockBuilder;

	protected function load($file) {
		$contents = file_get_contents($file);

		if (!preg_match('#(.*?)\n(/\*\*.*)#s',
		                $contents, $matches)) {
			throw new Exception("Could not parse file");
		}

		$spec = json_decode($matches[1], TRUE);
		if ($spec === NULL) {
			throw new Exception("Failed to decode JSON: " . json_last_error_msg());
		}

		$tests = [];
		if (!preg_match_all('#/\*\*(.*?)\n\*/s*#s', $matches[2], $testmatches)) {
			throw new Exception("Could not parse file");
		}
		foreach ($testmatches[1] as $test_text) {
			$test = [];
			if (!preg_match('#(.+?)\s*@#', $test_text, $matches)) {
				throw new Exception("Failed to parse test \"$test_text\"");
			}
			$test['description'] = $matches[1];
			if (preg_match('#@command (.+)#', $test_text, $matches)) {
				$test['command'] = explode(' ', "progname $matches[1]");
			} else {
				$test['command'] = ["progname"];
			}
			if (!preg_match('#@expects\n\s*(.*)#s', $test_text, $matches)) {
				throw new Exception("Failed to parse test \"$test_text\"");
			}
			$test['expected'] = $matches[1];

			$tests[] = $test;
		}
		return [$spec, $tests];
	}


	public function fileProvider() {
		$files = glob(__DIR__ . '/data/help/*.json');
		$x = array_combine(
			array_map(function($file) {
					return preg_replace('#^' . preg_quote(__DIR__) . '/#',
					                    '',
					                    $file);
				}, $files),
			array_map(function($file) {
					return [$file];
				}, $files));
		return $x;
	}


	/**
	 * @dataProvider fileProvider
	 */
	public function testFromFile($file) {
		list ($cmdlinespec, $tests) = $this->load($file);

		$cmdline = $this->buildMockCommandLine($cmdlinespec);

		$commands = (empty($cmdlinespec['test_commands'])
		             ? ['progname'] : $cmdlinespec['test_commands']);

		foreach ($tests as $test) {
			$hf = new HelpFormatter();
			$this->assertEquals($test['expected'],
			                    $hf->format($cmdline, $test['command']),
			                    "---\n/**\n$test[description]\n*/\n---");
		}
	}


	public function testDefaultUsageLabel() {
		$hf = new HelpFormatter();

		$this->assertEquals('Usage: ', $hf->getUsageLabel());
	}


	public function testSetUsageLabel() {
		$hf = new HelpFormatter();

		$hf->setUsageLabel('foobar');
		$this->assertEquals('foobar', $hf->getUsageLabel());
	}

}
