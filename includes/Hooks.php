<?php

namespace MediaWiki\Extension\LinkAttributes;

use Html;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use Title;

class Hooks {

	/**
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/HtmlPageLinkRendererEnd
	 * @param LinkRenderer $linkRenderer
	 * @param LinkTarget $target
	 * @param bool $isKnown
	 * @param string &$text
	 * @param string[] &$attribs
	 * @param string &$ret
	 * @return bool
	 */
	public static function onHtmlPageLinkRendererEnd(
		LinkRenderer $linkRenderer, LinkTarget $target, $isKnown, &$text, &$attribs, &$ret
	) {
		if ( $target ) {
			// Do nothing to internal links.
			return true;
		}
		$linkAtributeParser = new LinkAttributeParser( $text, $attribs );
		$text = $linkAtributeParser->getText();
		$title = Title::newFromText( $text );
		if ( $title === null ) {
			return true;
		}
		$attribs = $linkAtributeParser->getAttributes();
		$attribs['href'] = $title->exists() ? $title->getLocalURL() : $title->getEditURL();
		$ret = Html::element( 'a', $attribs, $text );
		return false;
	}

	/**
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/LinkerMakeExternalLink
	 * @param string &$url The URL of the external link
	 * @param string &$text The link text that would normally be displayed on the page
	 * @param string &$link The link HTML if you choose to override the default.
	 * @param string[] &$attribs Link attributes (added in MediaWiki 1.15, r48223)
	 * @param string $linktype Type of external link, e.g. 'free', 'text', 'autonumber'. Gets added
	 * to the css classes. (added in MediaWiki 1.15, r48226)
	 * @return bool Return false if you want to modify the HTML of external links.
	 */
	public static function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linktype ) {
		$mergedAttrs = array_merge( [ 'class' => "external $linktype", 'href' => $url ], $attribs );
		$linkAtributeParser = new LinkAttributeParser( $text, $mergedAttrs, true );
		$link = Html::element(
			'a',
			$linkAtributeParser->getAttributes(),
			$linkAtributeParser->getText()
		);
		return false;
	}
}
