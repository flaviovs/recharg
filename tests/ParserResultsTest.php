<?php

use Recharg\ParserResults;

class ParserResultsTest extends PHPUnit\Framework\TestCase {

	public function testPushGetCommands() {
		$pr = new ParserResults();

		$this->assertEquals([], $pr->getCommands());

		$pr->pushCommand('foo');
		$this->assertEquals(['foo'], $pr->getCommands());

		$pr->pushCommand('bar');
		$this->assertEquals(['foo', 'bar'], $pr->getCommands());
	}

	public function testPushGetOperands() {
		$pr = new ParserResults();

		$this->assertEquals([], $pr->getOperands());

		$pr->pushOperand('foo');
		$this->assertEquals(['foo'], $pr->getOperands());

		$pr->pushOperand('bar');
		$this->assertEquals(['foo', 'bar'], $pr->getOperands());
	}

	public function testArrayObjectForArguments() {
		$pr = new ParserResults();
		$pr['foo'] = 'bar';
		$pr['zee'] = 'lee';

		$this->assertEquals([
			                    'foo' => 'bar',
			                    'zee' => 'lee',
		                    ], (array)$pr);
	}

	public function testGetArguments() {
		$pr = new ParserResults();
		$pr['foo'] = 'bar';
		$pr['zee'] = 'lee';

		$this->assertEquals([
			                    'foo' => 'bar',
			                    'zee' => 'lee',
		                    ], $pr->getArguments());
	}

	public function testMergeOperandsEmpty() {
		$pr = new ParserResults();

		$pr->mergeOperands([]);

		$this->assertEquals([], $pr->getOperands());
	}

	public function testMergeOperands() {
		$pr = new ParserResults();

		$pr->pushOperand('foo');
		$pr->mergeOperands(['bar', 'lee']);

		$this->assertEquals([
			                    'foo',
			                    'bar',
			                    'lee',
		                    ], $pr->getOperands());
	}
}
