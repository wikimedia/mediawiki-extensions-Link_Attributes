<?php

namespace MediaWiki\Extension\LinkAttributes\Test;

use MediaWiki\Extension\LinkAttributes\LinkAttributeParser;
use PHPUnit\Framework\TestCase;

class LinkAttributeParserTest extends TestCase {

	/**
	 * @dataProvider provideSyntax
	 * @covers \MediaWiki\Extension\LinkAttributes\LinkAttributeParser
	 */
	public function testRelSyntax( $text, $attrs, $isExternal, $expectedText, $expectedAttrs ) {
		$parser = new LinkAttributeParser( $text, $attrs, $isExternal );
		static::assertSame( $expectedText, $parser->getText() );
		static::assertSame( $expectedAttrs, $parser->getAttributes() );
	}

	public function provideSyntax() {
		return [
			'no attributes' => [
				'text' => 'Foo',
				'attrs' => [],
				'isExternal' => false,
				'expectedText' => 'Foo',
				'expectedAttrs' => [],
			],
			'basic attrs' => [
				'text' => 'Foo ((test))',
				'attrs' => [],
				'isExternal' => false,
				'expectedText' => 'Foo',
				'expectedAttrs' => [ 'rel' => 'test' ],
			],
			'set and remove classes' => [
				'text' => 'Foo ((.new-class -.old-class))',
				'attrs' => [ 'class' => 'old-class' ],
				'isExternal' => false,
				'expectedText' => 'Foo',
				'expectedAttrs' => [ 'class' => 'new-class' ],
			],
			'prevent removal of nofollow from external links' => [
				'text' => 'Foo ((me .home   .main -nofollow))',
				'attrs' => [ 'rel' => 'nofollow' ],
				'isExternal' => true,
				'expectedText' => 'Foo',
				'expectedAttrs' => [ 'rel' => 'nofollow me', 'class' => 'home main' ],
			],
			'set rev' => [
				'text' => ' Main Page (( ~help )) ',
				'attrs' => [],
				'isExternal' => false,
				'expectedText' => 'Main Page',
				'expectedAttrs' => [ 'rev' => 'help' ],
			],
		];
	}
}
