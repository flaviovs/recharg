<?php

use Recharg\Lexer;
use Recharg\Token;

class LexerTest extends PHPUnit\Framework\TestCase {

	public function tokenDataProvider() {
		return [

			[
				['word'],
				[
					[Token::WORD, 'word']
				],
			],

			[
				['-a'],
				[
					[Token::SHORT_OPTION, '-a']
				],
			],

			[
				['-abc'],
				[
					[Token::SHORT_OPTION, '-abc']
				],
			],

			[
				['--long'],
				[
					[Token::LONG_OPTION, '--long']
				],
			],

			[
				['--'],
				[
					[Token::EOO]
				],
			],

			[
				[''],
				[
					[Token::WORD, '']
				],
			],

			[
				['-a', '-abc', '--long', '--', 'word', '-b', '--foo', '--'],
				[
					[Token::SHORT_OPTION, '-a'],
					[Token::SHORT_OPTION, '-abc'],
					[Token::LONG_OPTION, '--long'],
					[Token::EOO],
					[Token::WORD, 'word'],
					[Token::SHORT_OPTION, '-b'],
					[Token::LONG_OPTION, '--foo'],
					[Token::EOO],
				],
			],

		];
	}

	/**
	 * @dataProvider tokenDataProvider
	 */
	public function testGetNext(array $argv, array $result) {
		$l = new Lexer($argv);
		foreach ($result as $expected) {
			$t = $l->getNext();
			$this->assertEquals($expected[0], $t->getType());
			if (count($expected) == 2) {
				$this->assertEquals($expected[1], $t->getContent());
			} else {
				$this->assertNull($t->getContent());
			}
		}
	}
}
