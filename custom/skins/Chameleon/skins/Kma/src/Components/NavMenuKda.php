<?php

namespace Skins\Chameleon\Components;

use Linker;
use Sanitizer;
use Skins\Chameleon\IdRegistry;

/**
 * The NavMenu class.
 *
 * @author  Stephan Gambke
 * @since   1.0
 * @ingroup Skins
 */
class NavMenuKda extends Component {

	/**
	 * Builds the HTML code for this component
	 *
	 * @return string the HTML code
	 * @throws \MWException
	 */
	public function getHtml() {
		$ret = '';

		$sidebar = $this->getSkinTemplate()->getSidebar(
			[
				'search' => false,
				'toolbox' => false,
				'languages' => false,
			]
		);

		$flatten = $this->getMenusToBeFlattened();

		// create a dropdown for each sidebar box
		foreach ( $sidebar as $menuName => $menuDescription ) {

			// Ensure parent menu item has a valid id.
			$menuDescription['id'] = Sanitizer::escapeIdForAttribute(
				strtr( $menuDescription['id'], ' ', '-' )
			);
			/* @phan-suppress-next-line SecurityCheck-DoubleEscaped */
			$ret .= $this->getDropdownForNavMenu( $menuName, $menuDescription,
				array_search( $menuName, $flatten ) !== false );
		}


		return $ret;
	}

	/**
	 * Create a single dropdown
	 *
	 * @param string $menuName
	 * @param array $menuDescription
	 * @param bool $flatten
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function getDropdownForNavMenu( $menuName, $menuDescription, $flatten = false ) {
		// open list item containing the dropdown
		$ret = $this->indent() . '<!-- ' . $menuName . ' -->';

		if ( $flatten ) {

			$ret .= $this->buildMenuItemsForDropdownMenu( $menuDescription );

		} elseif ( !$this->hasSubmenuItems( $menuDescription ) ) {

			$ret .= $this->buildDropdownMenuStub( $menuDescription );

		} else {
			$ret .= $this->buildDropdownOpeningTags( $menuDescription ) .
					$this->buildMenuItemsForDropdownMenu( $menuDescription, 1 ) .
					$this->buildDropdownClosingTags();

		}

		return $ret;
	}

	/**
	 * @param array $menuDescription
	 * @param int $indent
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function buildMenuItemsForDropdownMenu( $menuDescription, $indent = 0 ) {
		// build the list of submenu items
		if ( $this->hasSubmenuItems( $menuDescription ) ) {

			$menuitems = '';
			$this->indent( $indent );

			foreach ( $menuDescription['content'] as $key => $item ) {
				$id = $item['id'] ?? '';

/*
				$menuitems .= $this->indent() . $this->getSkinTemplate()->makeListItem( $key, $item,
					[ 'tag' => 'div', 'class' => 'nav-item', 'link-class' => 'nav-link '. $id ] );
*/

				$menuitems .= $this->indent() . $this->getSkinTemplate()->makeListItem( $key, $item,
					[ 'tag' => 'li', 'class' => '', 'link-class' => '' ] );


			}

			$this->indent( - $indent );

			return $menuitems;

		} else {
			return $this->indent() . '<!-- empty -->';
		}
	}

	/**
	 * @param array $menuDescription
	 *
	 * @return bool
	 */
	protected function hasSubmenuItems( $menuDescription ) {
		return is_array( $menuDescription['content'] ) &&
			count( $menuDescription['content'] ) > 0;
	}

	/**
	 * @param array $menuDescription
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function buildDropdownMenuStub( $menuDescription ) {
		return $this->indent() . \Html::rawElement( 'div',
				[
					'class' => 'nav-item',
					'title' => Linker::titleAttrib( $menuDescription['id'] )
				],
				'<a href="#" class="nav-link">' . htmlspecialchars( $menuDescription['header'] ) . '</a>'
			);
	}

	/**
	 * @param array $menuDescription
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function buildDropdownOpeningTags( $menuDescription ) {
		// open list item containing the dropdown
		$ret = $this->indent() . \Html::openElement( 'div',
				[
					'class' => 'nav-item dropdown',
					'title' => Linker::titleAttrib( $menuDescription['id'] ),
				] );

		// add the dropdown toggle
		$ret .= $this->indent( 1 ) .
			'<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" data-boundary="viewport">' .
			htmlspecialchars( $menuDescription['header'] ) . '</a>';

		// open list of dropdown menu items
		$ret .=
			$this->indent() . \Html::openElement( 'div',
				[
					'class' => 'dropdown-menu ' . $menuDescription['id'],
					'id'    => IdRegistry::getRegistry()->getId( $menuDescription['id'] ),
				]
			);
		return $ret;
	}

	/**
	 * @return string
	 * @throws \MWException
	 */
	protected function buildDropdownClosingTags() {
		return $this->indent() . '</div>' .
			$this->indent( - 1 ) . '</div>';
	}

	/**
	 * @return string[]
	 */
	public function getMenusToBeFlattened() {
		$msg = \Message::newFromKey( 'skin-chameleon-navmenu-flatten' );

		if ( $msg->exists() ) {
			$flatten = array_map( 'trim', explode( ',', $msg->plain() ) );
		} elseif ( $this->getDomElement() !== null ) {
			$flatten =
				array_map( 'trim',
					explode( ';', $this->getDomElement()->getAttribute( 'flatten' ) ) );
		} else {
			$flatten = [];
		}

		return $flatten;
	}

}
