<?php
namespace Fina\Skin;

use MediaWiki\MediaWikiServices;
use Skins\Chameleon\Chameleon;

/** FINA's interface, using the supported Chameleon implementation. */
class Skin extends Chameleon {
    public $skinname = 'kma';

    public static function onRegistration(): void {
        $GLOBALS['wgHooks']['SetupAfterCache'][] = static function () {
            MediaWikiServices::getInstance()->getSkinFactory()->register('kma', 'FINA (Kma)',
                static fn () => new self([
                    'name' => 'kma',
                    'styles' => ['skins.chameleon', 'zzz.ext.bootstrap.styles'],
                    'responsive' => true,
                    'bodyOnly' => true,
                ])
            );
        };
    }

    public function addSkinModulesToOutput() {
        parent::addSkinModulesToOutput();
        $this->getOutput()->addModules('skins.kma.navigation');
        $this->getOutput()->addStyle('https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap');
    }

    public function getPageClasses($title) {
        return parent::getPageClasses($title) . ' skin-chameleon';
    }
}
