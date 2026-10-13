<?php
namespace Fina\Skin\Components;

use MediaWiki\Html\Html;
use Skins\Chameleon\Components\Component;
use Skins\Chameleon\Components\FooterIcons;
use Skins\Chameleon\Menu\MenuFactory;

/** FINA footer links, tools, attribution and logos. */
class Footer extends Component {
    public function getHtml() {
        $template = $this->getSkinTemplate();
        $menu = (new MenuFactory())->getMenuFromMessage('Footer');
        $menu->setMenuItemFormatter(static fn ($href, $class, $text, $depth, $children) =>
            '<li>' . Html::element('a', ['href' => $href, 'class' => $class], $text) . $children . '</li>'
        );
        $menu->setItemListFormatter(static fn ($items, $depth) => $depth === 0 ? $items : '<ul>' . $items . '</ul>');
        $links = $menu->getHtml();
        if ($links === '') {
            foreach ($template->getFooterLinks()['places'] ?? [] as $key) {
                $links .= '<li>' . $template->get($key) . '</li>';
            }
        }
        $tools = '';
        foreach ($template->get('sidebar')['TOOLBOX'] ?? [] as $key => $item) {
            $tools .= $template->makeListItem($key, $item);
        }
        $lastmod = $this->getSkin()->getTitle()->isMainPage() ? '' : $template->get('lastmod') . ' ';
        $icons = (new FooterIcons($template))->getHtml();
        return '<footer class="page-footer py-4"><div class="container">' .
            '<div class="row footer-row1 pt-4"><div class="col-md-12">' . $lastmod . $template->get('copyright') . '</div></div>' .
            '<div class="row footer-row2 py-2 pb-4 align-items-center">' .
            '<div class="col-md-3 py-2 text-md-start text-center"><ul>' . $links . '</ul></div>' .
            '<div class="col-md-3 py-2 text-center kma-toolbox"><button type="button" id="kma-toolbox-toggle" ' .
            'class="kma-menu-toggle" aria-controls="kma-toolbox-menu" aria-expanded="false">☰ ' .
            $this->getSkin()->msg('toolbox')->escaped() . '</button><ul class="nav-dropdown" id="kma-toolbox-menu" hidden>' . $tools . '</ul></div>' .
            '<div class="col-md-6 py-2 text-center">' . $icons . '</div></div></div></footer>';
    }
}
