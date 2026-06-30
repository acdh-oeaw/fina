<?php

namespace Skins\Chameleon\Components;

use DOMElement;
use Skins\Chameleon\IdRegistry;
use Linker;

class NavbarHorizontalKma extends Component {
	private $mHtml = null;
	private $htmlId = null;

	/**
	 * Builds the HTML code for this component
	 *
	 * @return String the HTML code
	 * @throws \MWException
	 */
	public function getHtml() {
		$navbar = 'taniarascia';	//'mdbootstrap';

		$this->getSkin()->getOutput()->addStyle('https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap');

		$navElements = $this->buildNavBarComponents();

		switch($navbar) {

			// https://github.com/samsono/Ace-Responsive-Menu
			case 'ace' :
				return '<nav>
        <!-- Menu Toggle btn-->
<span class="logo">
' . $this->getLogo() . '

</span>
        <div class="menu-toggle">
<!--
            <h3>Menu</h3>
-->

            <button type="button" id="menu-btn">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <!-- Responsive Menu Structure-->
        <!--Note: declare the Menu style in the data-menu-style="horizontal" (options: horizontal, vertical, accordion) -->
        <ul id="respMenu" class="ace-responsive-menu" data-menu-style="horizontal">

' . $navElements['left'] . '


  </ul>
    </nav>';

			// https://github.com/nicolettafbr/responsive-navbar
			case 'nicolettafabro' :
				return '<!--    Made by Nicoletta Fabro    -->
<nav id="nav-nicoletta" role="navigation">
  <div id="menuToggle">
    <!--
    A fake / hidden checkbox used as click receiver
    -->
    <input type="checkbox" />
    
    <!--
    Some spans acting as the hamburger icon.
    These will be animated into a cross when clicked.
    -->
    <span></span>
    <span></span>
    <span></span>
    
    <!--
    The menu itself.
    -->
<ul id="menu">' 
. $this->buildNavBarComponents()
. ' </ul>
  </div>
</nav>';

			// https://gist.github.com/taniarascia/56893ff29f64038dca91#file-nav-scss
			case 'taniarascia' :
				return '<section class="navigation">
  <div class="nav-container">
    <div class="brand">
      ' . $this->getLogo() . '
    </div>
    <nav class="navigation">

<ul class="nav-list-mobile">
<li class="icon"><a class="no-symbol page-tools-kda-search" href="#!"></a>'
 . ( $this->getSkin()->getUser()->isRegistered() ? "<li><a class=\"personal-tools-kda-edit\" href=\"#!\"></a></li>" : '' )
. '<li class="icon"><a class="no-symbol personal-tools-kda-user" href="#!"></a></li>
</ul>

      <div class="nav-mobile">
        <a id="nav-toggle" href="#!"><span></span></a>
      </div>

      <ul class="nav-list">

' . implode( '', $navElements['left'] ) . '

      </ul>


      <ul class="nav-list right">

' . implode( '', $navElements['right'] ) . '

      </ul>

    </nav>
<div style="clear:both"></div>

  </div>
</section>';
			
			case 'mdbootstrap' :
	
				return '<nav class="navbar navbar-expand-lg ">

  <!-- Navbar brand -->
  
      ' . $this->getLogo() . '

  <!-- Collapse button -->
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#basicExampleNav"
    aria-controls="basicExampleNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Collapsible content -->
  <div class="collapse navbar-collapse" id="basicExampleNav">


    <!-- Links -->
    <ul class="navbar-nav mr-auto">
     ' . $this->buildNavBarComponents() . '
    </ul>
    <!-- Links -->

    <form class="form-inline">
      <div class="md-form my-0">
        <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
      </div>
    </form>

    <ul class="navbar-nav mr-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" data-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">Dropdown</a>
        <div class="dropdown-menu dropdown-primary" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </li>
    </ul>

  </div>
  <!-- Collapsible content -->

</nav>';



		}

	}


	// ***edited
	/**
	 * @return string[] the resource loader modules needed by this component
	 */
	public function getResourceLoaderModules() {
		return 
			// [ 'skin.chameleonKma.ace' ]
			// [ 'skin.chameleonKma.nicolettafabro' ]
			[ 'skin.chameleonKma.taniarascia' ]
			// [ 'skin.chameleonKma.mdbootstrap' ]		

		;
	}

	/**
	 * @return string
	 * @throws \MWException
	 */
	protected function buildNavBarComponents() {
		$elements = $this->buildNavBarElementsFromDomTree();

		return $elements;

/*
		$head = $this->buildHead( $elements[ 'head' ] );

		// ***edited
		//if ( $this->isCollapsible() ) {
		if (false ) {
			$tail = $this->wrapDropdownMenu( $this->buildTail( $elements[ 'left' ],
				$elements[ 'right' ], 1 ) );
		} else {
			$tail = $this->buildTail( $elements[ 'left' ], $elements[ 'right' ] );
		}

		return $head . $tail;
*/
	}

	/**
	 * @return string[][]
	 * @throws \MWException
	 */
	protected function buildNavBarElementsFromDomTree() {
		$elements = [
			'head'  => [],
			'left'  => [],
			'right' => [],
		];

		/** @var DOMElement[] $children */
		$children = $this->getDomElement()->hasChildNodes() ? $this->getDomElement()->childNodes : [];

		// add components
		foreach ( $children as $node ) {
			$this->buildAndCollectNavBarElementFromDomElement( $node, $elements );
		}
		return $elements;
	}

	/**
	 * @param DOMElement $node
	 * @param array &$elements
	 *
	 * @throws \MWException
	 */
	protected function buildAndCollectNavBarElementFromDomElement( $node, &$elements ) {
		if ( $node instanceof DOMElement && $node->tagName === 'component' &&
			$node->hasAttribute( 'type' ) ) {

			$position = $node->getAttribute( 'position' );

			if ( !array_key_exists( $position, $elements ) ) {
				$position = 'left';
			}

			$indentation = 0;

			if ( $position !== 'head' && $this->isCollapsible() ) {
				$indentation++;
			}

			if ( $position === 'right' ) {
				$indentation++;
			}

			$this->indent( $indentation );
			$html = $this->buildNavBarElementFromDomElement( $node );
			$this->indent( -$indentation );

			$elements[ $position ][] = $html;

		// } else {
			// TODO: Warning? Error?
		}
	}

	/**
	 * @param \DomElement $node
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function buildNavBarElementFromDomElement( $node ) {
		return $this->getSkin()->getComponentFactory()->getComponent( $node,
			$this->getIndent() )->getHtml();
	}

	/**
	 * @param string[] $headElements
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function buildHead( $headElements ) {
		return implode( '', $headElements );
	}

	/**
	 * @param string[] $leftElements
	 * @param string[] $rightElements
	 * @param int $indent
	 *
	 * @return string
	 * @throws \MWException
	 */
	protected function buildTail( $leftElements = [], $rightElements = [], $indent = 0 ) {
		$this->indent( $indent );

		$tail = '';

		if ( $leftElements ) {

			// ***edited
/*
			$tail .= IdRegistry::getRegistry()->element( 'div', [ 'class' => 'navbar-nav' ],
				implode( '', $leftElements ), $this->indent() );
*/
			$tail .= implode( '', $leftElements );

		}

		if ( $rightElements ) {
			$tail .= IdRegistry::getRegistry()->element( 'div', [ 'class' => 'navbar-nav right' ],
				implode( '', $rightElements ), $this->indent() );
		}

		$this->indent( -$indent );

		return $tail;
	}

	/**
	 * @param string $tail
	 *
	 * @return string
	 * @throws \MWException
	 */
	private function wrapDropdownMenu( $tail ) {
		$id = IdRegistry::getRegistry()->getId();

		return $this->indent() .
			'<button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#' . $id .
			'">' . $this->getTogglerText() . '</button>' .
			IdRegistry::getRegistry()->element( 'div', [ 'class' => 'collapse navbar-collapse',
			'id' => $id ], $tail, $this->indent() );
	}

	/**
	 * @return mixed
	 */
	protected function isCollapsible() {
		return filter_var( $this->getAttribute( 'collapsible', 'true' ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * @return string
	 */
	protected function getTogglerText() {
		if ( !filter_var( $this->getAttribute( 'showTogglerText', 'false' ), FILTER_VALIDATE_BOOLEAN ) ) {
			return '';
		}
		return \Html::rawElement( 'span', [ 'class' => 'navbar-toggler-text' ],
			$this->getSkinTemplate()->getMsg( 'chameleon-toggler' )->escaped() );
	}



	////////////// logo component

	/**
	 * @return string
	 */
	protected function getLogo() {
		$logo = IdRegistry::getRegistry()->element( 'img',
			[
				'src' => $this->getSkinTemplate()->get( 'logopath', '' ),
				'alt' => $this->getSkinTemplate()->get( 'sitename', '' ),
			]
		);

		return $this->getLinkedLogo( $logo );
	}

	/**
	 * @param string $logo
	 *
	 * @return string
	 */
	protected function getLinkedLogo( $logo ) {
		if ( $this->shallLink() ) {

			$linkAttributes = array_merge(
				[ 'href' => $this->getLogoLink() ],
				Linker::tooltipAndAccesskeyAttribs( 'p-logo' )
			);

			return IdRegistry::getRegistry()->element( 'a', $linkAttributes, $logo );
		}

		return $logo;
	}

	/**
	 * @return string
	 */
	private function getLogoLink(): string {
		$navUrls = $this->getSkinTemplate()->get( 'nav_urls', [ 'mainpage' => [ 'href' => '#' ] ] );
		$mainPage = $navUrls['mainpage'] ?? [ 'href' => '#' ];
		return $mainPage['href'];
	}

	/**
	 * Return true if addLink attribute is unset or set to 'yes' in the Logo
	 * component description. Clicking on the logo should redirect to Main Page
	 * in that case. Else the logo should just display an inactive image.
	 *
	 * @return bool
	 */
	private function shallLink() {
		$domElement = $this->getDomElement();

		if ( $domElement === null ) {
			return true;
		}

		$attribute = $domElement->getAttribute( 'addLink' );

		return $attribute === '' || filter_var( $attribute, FILTER_VALIDATE_BOOLEAN );
	}


}
