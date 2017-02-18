<?php

use Recharg\CommandLine;
use Recharg\Option;
use Recharg\HelpFormatter;

class CommandLineTest extends \PHPUnit\Framework\TestCase {

	protected $saved_argv;

	public function setUp() {
		$this->saved_argv = $GLOBALS['argv'];
	}

	public function tearDown() {
		$GLOBALS['argv'] = $this->saved_argv;
	}

	public function testGetOptions() {
		$cl = new CommandLine();

		$this->assertEquals([], $cl->getOptions());

		$opt1 = $this->createMock(Option::class);
		$cl->addOption($opt1);
		$this->assertEquals([$opt1], $cl->getOptions());

		$opt2 = $this->createMock(Option::class);
		$cl->addOption($opt2);
		$this->assertEquals([$opt1, $opt2], $cl->getOptions());
	}

	public function testGetCommands() {
		$cl = new CommandLine();

		$this->assertEquals([], $cl->getCommands());

		$cmd1 = $this->createMock(CommandLine::class);
		$cl->addCommand('cmd1', $cmd1);
		$this->assertEquals(['cmd1' => $cmd1], $cl->getCommands());

		$cmd2 = $this->createMock(CommandLine::class);
		$cl->addCommand('cmd2', $cmd2);
		$this->assertEquals(['cmd1' => $cmd1, 'cmd2' => $cmd2],
		                    $cl->getCommands());
	}


	public function testGetUsage() {
		$cl = new CommandLine();

		$this->assertNull($cl->getUsage());

		$cl->setUsage('foobar');
		$this->assertEquals('foobar', $cl->getUsage());
	}


	public function testGetSummary() {
		$cl = new CommandLine();

		$this->assertNull($cl->getSummary());

		$cl->setSummary('foobar');
		$this->assertEquals('foobar', $cl->getSummary());
	}


	public function testGetDescription() {
		$cl = new CommandLine();

		$this->assertNull($cl->getDescription());

		$cl->setDescription('foobar');
		$this->assertEquals('foobar', $cl->getDescription());
	}


	public function testGetFooter() {
		$cl = new CommandLine();

		$this->assertNull($cl->getFooter());

		$cl->setFooter('foobar');
		$this->assertEquals('foobar', $cl->getFooter());
	}


	public function testGetOperands() {
		$cl = new CommandLine();

		$this->assertNull($cl->getOperands());

		$cl->setOperands('foobar');
		$this->assertEquals('foobar', $cl->getOperands());
	}


	public function testGetCommandAtOneLevel() {
		$cl = new CommandLine();
		$this->assertSame($cl, $cl->getCommand([]));
	}


	public function testGetCommandAtSecondLevel() {
		$cl = new CommandLine();

		$s1 = new CommandLine();
		$s2 = new CommandLine();
		$s3 = new CommandLine();
		$cl->addCommand('s1', $s1);
		$cl->addCommand('s2', $s2);
		$cl->addCommand('s3', $s3);

		$this->assertSame($s1, $cl->getCommand(['s1']));
		$this->assertSame($s2, $cl->getCommand(['s2']));
		$this->assertSame($s3, $cl->getCommand(['s3']));
	}


	public function testGetCommandAtThirdLevel() {
		$cl = new CommandLine();

		$s1 = new CommandLine();
		$s2 = new CommandLine();

		$s11 = new CommandLine();
		$s12 = new CommandLine();

		$s21 = new CommandLine();
		$s22 = new CommandLine();

		$s1->addCommand('s11', $s11);
		$s1->addCommand('s12', $s12);

		$s2->addCommand('s21', $s21);
		$s2->addCommand('s22', $s22);

		$cl->addCommand('s1', $s1);
		$cl->addCommand('s2', $s2);

		$this->assertSame($s1, $cl->getCommand(['s1']));
		$this->assertSame($s2, $cl->getCommand(['s2']));

		$this->assertSame($s11, $cl->getCommand(['s1', 's11']));
		$this->assertSame($s12, $cl->getCommand(['s1', 's12']));
		$this->assertSame($s21, $cl->getCommand(['s2', 's21']));
		$this->assertSame($s22, $cl->getCommand(['s2', 's22']));
	}

	public function testGetHelpFormatter() {
		$cl = new CommandLine();

		$this->assertNull($cl->getHelpFormatter());

		$hf = $this->createMock(HelpFormatter::class);
		$cl->setHelpFormatter($hf);
		$this->assertSame($hf, $cl->getHelpFormatter());
	}

}
