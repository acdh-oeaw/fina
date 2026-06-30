<?php

namespace Skins\Chameleon\Components;

use Skins\Chameleon\IdRegistry;
use Skins\Chameleon\Menu\MenuFactory;
use Sanitizer;

class FooterKma extends Component {

	/**
	 * Builds the HTML code for this component
	 *
	 * @return String the HTML code
	 * @throws \MWException
	 */
	public function getHtml() {

/*
		return $this->indent() . '<!-- places -->' .
			IdRegistry::getRegistry()->element(
				'div',
				[ 'id' => 'footer-places', 'class' => $this->getClassString() ],
				implode( $this->getFooterLinks() ),
				$this->indent()
			);
*/
		
		// ***first get menu from the message (pagename) attribute
		$menu = $this->getMenu();

		$links = [];
		$menu->setMenuItemFormatter( function ( $href, $class, $text, $depth, $subitems ) use ( &$links ) {
			$href = Sanitizer::cleanUrl( $href );
			$text = htmlspecialchars( $text );
/*
			if ( $depth === 1 && !empty( $subitems ) ) {
				return "<li class=\"\"><a class=\"$class\" href=\"#!\">$text</a>$subitems</li>";
			} else {
				return "<li class=\"\"><a class=\"$class\"  href=\"$href\">$text</a>$subitems</li>";
			}
*/

			if ( $depth === 1) {
				if ( !empty( $subitems ) ) {
					return "<div class=\"col-md-3 mt-4\"><h5 class=\"font-weight-bold text-uppercase\">$text</h5>$subitems</h5></div>";
				} else {
					$links[] = "<a class=\"$class\" href=\"$href\">$text</a>";
					return '';
				}
			} else {
				return "<li class=\"\"><a class=\"$class\" href=\"$href\">$text</a>$subitems</li>";
			}

		} );

		$menu->setItemListFormatter( function ( $rawItemsHtml, $depth ) {
			if ( $depth === 0 ) {
				return $rawItemsHtml;
			} elseif ( $depth === 1 ) {
				return "<ul class=\"list-unstyled\">$rawItemsHtml</ul>";
			} else {
				return "<ul class=\"nested\">$rawItemsHtml</ul>";
			}
		} );

		$menuHtml = $menu->getHtml();

		// then retrieve the footer "places" links
		if ( empty( $menuHtml ) && empty( $links ) ) {
			$links = $this->getFooterLinks();
		}

		$linkPerColumn = 4;
		$columnsNoTitle = [];
		$columns = array_chunk( $links, $linkPerColumn );

		foreach ( $columns as $items ) {
			$columnsNoTitle[] = "<div class=\"col-md-3 mt-4\"><ul class=\"list-unstyled\">"
					. implode( array_map( static function( $value ) {
						return "<li class=\"list-unstyled\">$value</li>";
			}, $items ) ) . '</ul></div>';
		}

		// $footerIcons = new FooterIcons();
		// $this->getSkin()->getCopyright()
		// $showIcons = false;
		$ret = '';

		// @see https://mdbootstrap.com/docs/b4/jquery/navigation/footer/
		$ret .= '<footer class="page-footer">';
			$ret .= $this->indent( 1 );
			$ret .= '<div class="container text-center my-4">';
				$ret .= $this->indent( 1 );
				$ret .= '<!-- Grid row -->';
				$ret .= '<div class="row justify-content-center align-items-top">';

					$ret .= $this->indent( 1 );
					$ret .= '<div class="col-md-3" mt-4>';
						$ret .= $this->indent( 1 );
						$ret .= '<img height="150"src="/footer-logo.png" />';
					$ret .= '</div>';

				$ret .= implode( $columnsNoTitle );
				$ret .= $menuHtml;

				$ret .= '</div>';
				$ret .= '<!-- Grid row -->';
			$ret .= '</div>';
		$ret .= '</footer>';

		return $ret;
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


	/**
	 * @return array
	 * @throws \MWException
	 */
	private function getFooterLinks() {
		$footerlinks = $this->getSkinTemplate()->getFooterLinks();

		if ( !array_key_exists( 'places', $footerlinks ) ) {
			return [];
		}

		// $this->indent( 1 );

/*
		$links = [];
		foreach ( $footerlinks[ 'places' ] as $key ) {
			$links[] = $this->indent() . '<div>' . $this->getSkinTemplate()->get( $key ) . '</div>';
		}
*/
		$links = [];
		foreach ( $footerlinks[ 'places' ] as $key ) {
			$links[] = $this->getSkinTemplate()->get( $key );
		}

		// $this->indent( -1 );
		return $links;
	}


	//////////////////	FooterIcons /////////////////////


	/**
	 * Builds the HTML code for this component
	 *
	 * @return String the HTML code
	 * @throws \MWException
	 */
	public function FooterIconsGetHtml() {
		return $this->indent() . '<!-- footer icons -->' .
			IdRegistry::getRegistry()->element(
				'div',
				[ 'id' => 'footer-icons', 'class' => $this->getClassString() ],
				implode( $this->getIcons() ),
				$this->indent()
			);
	}

	/**
	 * @return string[]
	 * @throws \MWException
	 */
	private function getIcons() {
		$this->indent( 1 );

		$lines = [];
		$blocks = $this->getSkinTemplate()->getFooterIconsWithImage() ?? [];

		foreach ( $blocks as $blockName => $footerIcons ) {

			$lines[] = $this->indent() . '<!-- ' . htmlspecialchars( $blockName ) . ' -->';

			foreach ( $footerIcons as $icon ) {
				$lines[] = 
			// $this->indent() . '<div>' .
					$this->getSkinTemplate()->getSkin()->makeFooterIcon( $icon )
				;
				 //. '</div>';
			}

		}

		$this->indent( -1 );
		return $lines;
	}
}
