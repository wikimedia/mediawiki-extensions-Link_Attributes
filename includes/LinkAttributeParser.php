<?php

namespace MediaWiki\Extension\LinkAttributes;

use HtmlArmor;

class LinkAttributeParser {

	/** @var string */
	protected $text;

	/** @var string[] */
	protected $attrs;

	/**
	 * @param string|HtmlArmor $text
	 * @param string[] $attrs
	 * @param bool $isExternal
	 */
	public function __construct( $text, $attrs, $isExternal = false ) {
		$actualText = $text instanceof HtmlArmor ? HtmlArmor::getHtml( $text ) : $text;
		$this->text = trim( $actualText );
		$this->attrs = $attrs;

		// Initial extraction of attribute part of text.
		$hasParentheses = preg_match( '/^(.+)\(\((.*)\)\).*$/', $this->text, $matches );
		if ( !$hasParentheses ) {
			// No double-parentheses found; nothing more to do.
			return;
		}

		// Store the link text.
		$this->text = trim( $matches[1] );

		$attrs = array_merge( [
			'rev' => '',
			'rel' => '',
			'class' => '',
		], $attrs );

		// Extract whitespace-delimited parts in double-parentheses.
		$attrVals = preg_split( '/\s+/', $matches[ 2 ] );
		foreach ( $attrVals as $val ) {
			// Prevent removal of rel=nofollow on external links.
			if ( $isExternal && ( strtolower( $val ) === '-nofollow' ) ) {
				continue;
			}

			if ( ( substr( $val, 0, 2 ) == '-~' || substr( $val, 0, 2 ) == '~-' ) ) {
				// Remove rev
				$attrs[ 'rev' ] = str_ireplace( substr( $val, 2 ), '', $attrs[ 'rev' ] );
			} elseif ( ( substr( $val, 0, 2 ) == '-.' || substr( $val, 0, 2 ) == '.-' ) ) {
				// Remove class
				$attrs[ 'class' ] = str_ireplace( substr( $val, 2 ), '', $attrs[ 'class' ] );
			} elseif ( ( substr( $val, 0, 1 ) == '-' ) ) {
				// Remove rel
				$attrs['rel' ] = str_ireplace( substr( $val, 1 ), '', $attrs[ 'rel' ] );
			} elseif ( substr( $val, 0, 1 ) == '~' ) {
				// Add rev
				$attrs['rev' ] .= ' ' . substr( $val, 1 );
			} elseif ( substr( $val, 0, 1 ) == '.' ) {
				// Add class
				$attrs['class'] .= ' ' . substr( $val, 1 );
			} else {
				// Add rel
				$attrs['rel'] .= ' ' . $val;
			}
		}

		// Remove empty values and leading, trailing, or too much internal whitespace.
		$this->attrs = array_filter( array_map( 'trim', preg_replace( '/\s+/', ' ', $attrs ) ) );
	}

	/**
	 * @return string[]
	 */
	public function getAttributes() {
		return $this->attrs;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}
}
