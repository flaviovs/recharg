<?php

use Recharg\CommandLine;
use Recharg\CommandLineParser;
use Recharg\Option;

class CommandLineParserTest extends PHPUnit\Framework\TestCase {


	public function testGetName() {
		$cmdline = $this->createMock(CommandLine::class);
		$cmdline
			->method('getOptions')
			->willReturn([]);

		$clp = new CommandLineParser($cmdline, 'foobar');

		$this->assertEquals('foobar', $clp->getName());
	}


	public function testConversion() {

		$optfoo = $this->createMock(Option::class);
		$optfoo
			->method('getName')
			->willReturn('foo');
		$optfoo
			->method('getMatches')
			->willReturn(['--foo']);
		$optfoo
			->method('acceptsArguments')
			->willReturn(TRUE);
		$optfoo
			->method('convert')
			// Our mock convert() method with convert x to "x + x".
			->will($this->returnCallback(function($data) {
						return $data + $data;
					}));

		$cmdline = $this->createMock(CommandLine::class);
		$cmdline
			->method('getOptions')
			->willReturn([$optfoo]);

		$cp = new CommandLineParser($cmdline, 'foobar');

		$res = $cp->parse(['--foo=1']);
		$this->assertSame($res['foo'], 2);

		$res = $cp->parse(['--foo=2']);
		$this->assertSame($res['foo'], 4);

		$res = $cp->parse(['--foo=3']);
		$this->assertSame($res['foo'], 6);
	}
}
