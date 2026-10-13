<?php
namespace Fina\Skin\Components;

use MediaWiki\Html\Html;
use Skins\Chameleon\Components\Component;

/** Preserve the authenticated user's page tabs and persistent visibility toggle. */
class PageTools extends Component {
    public function getHtml() {
        if (!$this->getSkin()->getUser()->isRegistered()) {
            return '';
        }
        $groups = '';
        foreach ($this->getSkinTemplate()->get('content_navigation', []) as $category => $tabs) {
            $items = '';
            foreach ($tabs as $key => $tab) {
                if (!($tab['redundant'] ?? false)) {
                    $items .= $this->getSkinTemplate()->makeListItem($key, $tab);
                }
            }
            if ($items === '') {
                continue;
            }
            if ($category === 'actions') {
                $items = '<li>' . Html::element('button', [
                    'type' => 'button', 'class' => 'kma-menu-toggle', 'aria-expanded' => 'false',
                ], $this->getSkin()->msg('actions')->text()) .
                    '<ul class="nav-dropdown" hidden>' . $items . '</ul></li>';
            }
            $groups .= Html::rawElement('ul', ['id' => 'p-' . $category, 'class' => 'nav-list tab-group'], $items);
        }
        return Html::rawElement('nav', [
            'id' => 'p-contentnavigation', 'class' => 'p-contentnavigation edit justify-content-between',
            'aria-label' => $this->getSkin()->msg('actions')->text(),
            'hidden' => (bool)$this->getSkin()->getRequest()->getCookie('kmaskin-show-nav-edit'),
        ], $groups);
    }
}
