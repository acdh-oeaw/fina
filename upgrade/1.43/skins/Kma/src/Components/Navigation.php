<?php
namespace Fina\Skin\Components;

use MediaWiki\Html\Html;
use Skins\Chameleon\Components\Component;
use Skins\Chameleon\Menu\MenuFactory;

/** Port of FINA's NavbarHorizontalKma, MenuKma, SearchBarKma and PersonalToolsKma. */
class Navigation extends Component {
    public function getHtml() {
        $template = $this->getSkinTemplate();
        $skin = $this->getSkin();
        $logo = Html::rawElement('a', ['href' => $template->get('nav_urls')['mainpage']['href']],
            Html::element('img', ['src' => $GLOBALS['wgLogo'], 'alt' => 'FINA'])
        );
        $search = Html::rawElement('form', [
            'id' => 'searchform', 'class' => 'mw-search', 'action' => $GLOBALS['wgScript'], 'role' => 'search',
        ], Html::hidden('title', $template->get('searchtitle')) .
            $skin->makeSearchInput(['id' => 'searchInput', 'type' => 'search', 'class' => 'form-control',
                'aria-label' => $skin->msg('search')->text()])
        );
        $tools = '';
        foreach ($template->getPersonalTools() as $key => $item) {
            $tools .= $template->makeListItem($key, $item);
        }
        $controls = '<li class="icon">' . $this->button('search', 'page-tools-kda-search', 'p-search') . '</li>';
        if ($skin->getUser()->isRegistered()) {
            $controls .= '<li class="icon">' . $this->button('kma-toggle-page-tools', 'personal-tools-kda-edit', 'p-contentnavigation') . '</li>';
        }
        $controls .= '<li class="icon">' . $this->button('kma-personal-tools', 'personal-tools-kda-user', 'kma-personal-tools') . '</li>';
        $controls .= '<li class="nav-mobile"><button type="button" id="nav-toggle" aria-controls="kma-main-menu" aria-expanded="false" aria-label="' .
            $skin->msg('kma-toggle-navigation')->escaped() . '"><span></span></button></li>';

        return '<section class="navigation"><div class="nav-container"><div class="brand">' . $logo . '</div>' .
            '<nav class="navigation" id="mw-navigation" aria-label="' . $skin->msg('kma-navigation')->escaped() . '">' .
            '<ul class="nav-list" id="kma-main-menu">' . $this->menu() . '</ul>' .
            '<ul class="nav-list right kma-controls">' . $controls . '</ul>' .
            '<div class="kma-search-wrapper" id="p-search" hidden>' . $search . '</div>' .
            '<ul class="nav-dropdown" id="kma-personal-tools" hidden>' . $tools . '</ul></nav>' .
            '<div style="clear:both"></div></div></section>';
    }

    private function button(string $message, string $class, string $target): string {
        return Html::element('button', [
            'type' => 'button', 'class' => 'kma-control no-symbol ' . $class,
            'aria-label' => $this->getSkin()->msg($message)->text(),
            'aria-controls' => $target, 'aria-expanded' => 'false',
        ]);
    }

    private function menu(): string {
        $menu = (new MenuFactory())->getMenuFromMessage('secondary-menu');
        $menu->setMenuItemFormatter(static function ($href, $class, $text, $depth, $subitems) {
            if ($subitems !== '') {
                return '<li>' . Html::element('button', [
                    'type' => 'button', 'class' => 'kma-menu-toggle', 'aria-expanded' => 'false',
                ], $text) . $subitems . '</li>';
            }
            return '<li>' . Html::element('a', ['href' => $href, 'class' => $class], $text) . '</li>';
        });
        $menu->setItemListFormatter(static fn ($items, $depth) => $depth === 0 ? $items :
            Html::rawElement('ul', ['class' => 'nav-dropdown', 'hidden' => true], $items)
        );
        return $menu->getHtml();
    }
}
