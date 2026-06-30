<?php

namespace Skins\Chameleon\Components;

use Action;
use MediaWiki\MediaWikiServices;
use Skins\Chameleon\ChameleonTemplate;
use Skins\Chameleon\IdRegistry;

/**
 * The PageTools class.
 *
 * An unordered list containing content navigation links (Page, Discussion,
 * Edit, History, Move, ...)
 *
 * The tab list is a list of lists: '<ul id="p-contentnavigation">
 *
 * @author  Stephan Gambke
 * @since   1.0
 * @ingroup Skins
 */
class PageToolsNavKma extends Component {

	private $mFlat = false;

	/**
	 * PageTools constructor.
	 *
	 * @param ChameleonTemplate $template
	 * @param \DOMElement|null $domElement
	 * @param int $indent
	 *
	 * @throws \MWException
	 */
	public function __construct( ChameleonTemplate $template, \DOMElement $domElement = null,
		$indent = 0 ) {
		parent::__construct( $template, $domElement, $indent );
		// ***edited
		// $this->addClasses( 'pagetools' );
	}

	/**
	 * Builds the HTML code for this component
	 *
	 * @return string the HTML code
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function getHtml() {

		if( !$this->getSkin()->getUser()->isRegistered() ) {
			return '';
		}

		$toolGroups = $this->getToolGroups();

		if ( $toolGroups === [] ) {
			return '';
		}

		$editing = $this->getSkin()->getRequest()->getCookie('kmaskin-show-nav-edit');

		return $this->indent() . '<!-- Content navigation -->' .
			IdRegistry::getRegistry()->element(
				// ***edited
				// 'div',
				 'nav',
				[ 'class' => $this->getClassString() . ' edit justify-content-between' . ( $editing ? ' hide' : '' ) , 'id' => 'p-contentnavigation' ],
				implode( $toolGroups ),
				$this->indent()
			);

	}

	/**
	 * @return string[]
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	protected function getToolGroups() {
		$toolGroups = [];

		$this->indent( 1 );

		foreach ( $this->getContentNavigation() as $category => $tabsDescription ) {

			$toolGroup = $this->getToolGroup( $category, $tabsDescription );

			if ( $toolGroup !== null ) {
				$toolGroups[] = $toolGroup;
			}
		}

		$this->indent( -1 );

		return $toolGroups;
	}

	/**
	 * @return array
	 */
	private function getContentNavigation(): array {
		$contentNavigation = $this->getPageToolsStructure();

		$this->removeSelectedNamespaceIfNeedBe( $contentNavigation );
		$this->removeDiscussionLinkIfNeedBe( $contentNavigation );

		return $contentNavigation;
	}

	/**
	 * @param array &$contentNavigation
	 */
	private function removeSelectedNamespaceIfNeedBe( array &$contentNavigation ) {
		if ( $this->hideSelectedNamespace() ) {
			unset( $contentNavigation[ 'namespaces' ][ $this->getNamespaceKey() ] );
		}
	}

	/**
	 * @param array &$contentNavigation
	 */
	private function removeDiscussionLinkIfNeedBe( array &$contentNavigation ) {
		if ( $this->hideDiscussionLink() ) {
			$talkNamespaceKey = $this->getNamespaceKey() === 'main' ? 'talk' :
				$this->getNamespaceKey() . '_talk';

			unset( $contentNavigation[ 'namespaces' ][ $talkNamespaceKey ] );
		}
	}

	/**
	 * @return mixed
	 */
	public function getPageToolsStructure() {
		return $this->getSkinTemplate()->get( 'content_navigation', null );
	}

	/**
	 * @param mixed $pageToolsStructure
	 *
	 * @return void
	 */
	public function setPageToolsStructure( $pageToolsStructure ) {
		$this->getSkinTemplate()->set( 'content_navigation', $pageToolsStructure );
	}

	private function hideSelectedNamespace(): bool {
		return $this->attributeIsYes( 'hideSelectedNameSpace' )
			&& Action::getActionName( $this->getSkin() ) === 'view';
	}

	/**
	 * @return bool
	 */
	private function hideDiscussionLink(): bool {
		return $this->attributeIsYes( 'hideDiscussionLink' );
	}

	/**
	 * @param string $attributeName
	 *
	 * @return bool
	 */
	private function attributeIsYes( string $attributeName ): bool {
		return $this->getDomElement() !== null &&
			filter_var( $this->getDomElement()->getAttribute( $attributeName ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Generate strings used for xml 'id' names in tabs
	 *
	 * Based on MW's Title::getNamespaceKey()
	 *
	 * Difference: This function here reports the actual namespace while the
	 * one in Title reports the subject namespace, i.e. no talk namespaces
	 *
	 * @return string
	 * @throws \ConfigException
	 */
	public function getNamespaceKey() {
		// Gets the subject namespace of this title
		$title = $this->getSkinTemplate()->getSkin()->getTitle();

		$namespaceKey = MediaWikiServices::getInstance()->getNamespaceInfo()->getCanonicalName(
			$title->getNamespace()
		);

		if ( $namespaceKey === false ) {
			$namespaceKey = $title->getNsText();
		}

		// Makes namespace key lowercase
		$namespaceKey =
			MediaWikiServices::getInstance()->getContentLanguage()->lc( $namespaceKey );

		if ( $namespaceKey === '' ) {
			return 'main';
		} elseif ( $namespaceKey === 'file' ) {
			return 'image';
		}

		return $namespaceKey;
	}

	/**
	 * @param string $category
	 * @param mixed[][] $tabsDescription
	 *
	 * @return string|null
	 * @throws \MWException
	 */
	protected function getToolGroup( $category, $tabsDescription ) {
		if ( empty( $tabsDescription ) ) {
			return null;
		}

		$comment = $this->indent() . "<!-- $category -->";

		if ( $this->mFlat ) {
			return $comment . implode( $this->getToolsForGroup( $tabsDescription ) );
		}



		$ret = implode( $this->getToolsForGroup( $tabsDescription, 2 ) );

		if ( $category === 'actions' ) {
			$ret = "<li class=\"\"><a class=\"\" href=\"#!\">actions</a><ul style=\"right:0\" class=\"nav-dropdown\">$ret</ul></li>";
		}


return IdRegistry::getRegistry()->element( 
					// ***edited
					// 'div',
					'ul',
					[ 
'id' => 'p-' . $category,
'class' => 'nav-list tab-group' ],

					// implode( $this->getToolsForGroup( $tabsDescription, 2 ) ),
					$ret,
					$this->indent( 1 )
				);


		return $comment .
			IdRegistry::getRegistry()->element( 'div',
				[ 'id' => 'p-' . $category,

			// ***edited
					'class' => 'mw-portlet mw-portlet-namespaces vector-menu vector-menu-tabs'
				],

				IdRegistry::getRegistry()->element( 
					// ***edited
					// 'div',
					'ul',
					[ 'class' => 'tab-group' ],

					// implode( $this->getToolsForGroup( $tabsDescription, 2 ) ),
					$ret,
					$this->indent( 1 )
				),
				$this->indent( -1 )
			);


		// return implode( $this->getToolsForGroup( $tabsDescription, 2 ) );

	}

	/**
	 * @param array $tabsDescription
	 *
	 * @param int $indent
	 *
	 * @return array
	 * @throws \MWException
	 */
	protected function getToolsForGroup( $tabsDescription, $indent = 0 ) {
		$tabs = [];
		$this->indent( $indent );

		foreach ( $tabsDescription as $key => $tabDescription ) {
			$tabs[] = $this->getTool( $tabDescription, $key );
		}

		$this->indent( -$indent );

		return $tabs;
	}

	/**
	 * @param mixed[] $tabDescription
	 * @param string $key
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function getTool( $tabDescription, $key ) {
		// skip redundant links (i.e. the 'view' link)
		// TODO: make this dependent on an option
		if ( array_key_exists( 'redundant', $tabDescription ) && $tabDescription[ 'redundant' ]
			=== true ) {
			return '';
		}

		// apply a link class if specified, e.g. for the currently active namespace

		$options = [
			// 'tag' => 'div'
			'tag' => 'li'
		];
		if ( array_key_exists( 'class', $tabDescription ) ) {
			$options[ 'link-class' ] = $tabDescription[ 'class' ].' '.$tabDescription['id'];
		}

		return $this->indent() .
			$this->getSkinTemplate()->makeListItem( $key, $tabDescription, $options );
	}

	/**
	 * Set the page tool menu to have submenus or not
	 *
	 * @param bool $flat
	 */
	public function setFlat( $flat ) {
		$this->mFlat = $flat;
	}

	/**
	 * Set redundant tools
	 *
	 * @param string|string[] $tools
	 */
	public function setRedundant( $tools ) {
		$tools = (array)$tools;

		$pageToolsStructure = $this->getPageToolsStructure();

		foreach ( $tools as $tool ) {
			foreach ( $pageToolsStructure as $group => $groupStructure ) {
				if ( array_key_exists( $tool, $groupStructure ) ) {
					$pageToolsStructure[ $group ][ $tool ][ 'redundant' ] = true;
				}
			}
		}

		$this->setPageToolsStructure( $pageToolsStructure );
	}

}
