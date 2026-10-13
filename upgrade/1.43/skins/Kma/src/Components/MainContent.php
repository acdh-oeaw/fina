<?php
namespace Fina\Skin\Components;

use Skins\Chameleon\Components\MainContent as ChameleonMainContent;
use Skins\Chameleon\Components\SiteNotice;

/** Keep the site notice inside FINA's padded content area, as in the old skin. */
class MainContent extends ChameleonMainContent {
    public function getHtml() {
        $html = parent::getHtml();
        $notice = (new SiteNotice($this->getSkinTemplate()))->getHtml();
        return preg_replace_callback('/<div[^>]+id="content"[^>]*>/', static fn ($match) => $match[0] . $notice, $html, 1);
    }
}
