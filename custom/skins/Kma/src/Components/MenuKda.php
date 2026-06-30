<?php

namespace Skins\Chameleon\Components;

use Sanitizer;
use Skins\Chameleon\Menu\MenuFactory;

/**
 * Class Menu
 *
 * @author Stephan Gambke
 * @since 1.0
 * @ingroup Skins
 */
class MenuKda extends Component {

	/**
	 * Builds the HTML code for this component
	 *
	 * @return String the HTML code
	 * @throws \MWException
	 */
	public function getHtml() {
		if ( $this->getDomElement() === null ) {
			return '';
		}

		$menu = $this->getMenu();

		$menu->setMenuItemFormatter( function ( $href, $class, $text, $depth, $subitems ) {
			$href = Sanitizer::cleanUrl( $href );
			$text = htmlspecialchars( $text );

			// @codingStandardsIgnoreStart

/*
			if ( $depth === 1 && !empty( $subitems ) ) {
				return "<div class=\"nav-item dropdown\"><a class=\"nav-link dropdown-toggle $class\" href=\"#\"  data-toggle=\"dropdown\"  data-boundary=\"viewport\">$text</a>$subitems</div>";
			} else {
				return "<div class=\"nav-item\"><a class=\"nav-link $class\"  href=\"$href\">$text</a>$subitems</div>";
			}
*/

			if ( $depth === 1 && !empty( $subitems ) ) {
				return "<li class=\"\"><a class=\"$class\" href=\"#!\">$text</a>$subitems</li>";
			} else {
				return "<li class=\"\"><a class=\"$class\"  href=\"$href\">$text</a>$subitems</li>";
			}

			// @codingStandardsIgnoreEnd
		} );

		$menu->setItemListFormatter( function ( $rawItemsHtml, $depth ) {
			if ( $depth === 0 ) {
				return $rawItemsHtml;
			} elseif ( $depth === 1 ) {
				return "<ul class=\"nav-dropdown\">$rawItemsHtml</ul>";
			} else {
				//return "<div>$rawItemsHtml</div>";
				return "<ul>$rawItemsHtml</ul>";
			}
		} );

		return $menu->getHtml();
	}

	/**
	 * @return \Skins\Chameleon\Menu\Menu
	 * @throws \MWException
	 */
	public function getMenu() {
		$domElement = $this->getDomElement();
		$msgKey = $domElement->getAttribute( 'message' );

		$menuFactory = new MenuFactory();

		if ( empty( $msgKey ) ) {
			return $menuFactory->getMenuFromMessageText( $domElement->textContent );
		} else {
			return $menuFactory->getMenuFromMessage( $msgKey );

		}
	}
}
