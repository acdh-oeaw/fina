<?php
// FINA presentation shared by the local 1.43 build and the eventual production image.
wfLoadExtension('Kma');
wfLoadSkin('Kma');
$wgDefaultSkin = 'kma';
$wgLogo = "$wgScriptPath/fina-logo.png";
$wgFavicon = "$wgScriptPath/favicon.png";
$egChameleonLayoutFile = "$IP/skins/Kma/layout.xml";
$egChameleonThemeFile = "$IP/skins/Kma/resources/variables.scss";
$egChameleonExternalStyleModules = [
    "$IP/skins/Kma/resources/navigation.scss" => 'afterMain',
    "$IP/skins/Kma/resources/fina.scss" => 'afterMain',
    "$IP/skins/Kma/resources/port.scss" => 'afterMain',
];
$egChameleonExternalStyleVariables = [
    'body-bg'                     => '#ffffff',
    'font-family-base'            => "Roboto,sans-serif",
    'headings-color'              => '#0047bb',
    'headings-font-family'        => "Alegreya,serif",
    'dark'                        => '#5c6784',
    'light'                       => '#f5f5f5',
    'primary'                     => '#00122f',
    'secondary'                   => '#9eaeb5',
    'info'                        => '#0355ad',
    'danger'                      => '#910a00',
    'warning'                     => '#e39f00',
    'success'                     => '#009e4c',
    'cmln-navbar-logo-height'     => '1.0rem',
    'cmln-navbar-bg-color'        => 'light',
    'cmln-search-bar-btn-color'   => 'light',
    'navbar-light-color'          => '#0047bb',
    'navbar-light-hover-color'    => '#0099ff',
    'navbar-light-active-color'   => '#0355ad',
    'navbar-padding-y'            => '1.0rem',
    'dropdown-link-hover-bg'      => '#0355ad',
    'dropdown-link-hover-color'   => '#f5f5f5',
    'dropdown-link-active-color'  => '#ffffff',
    'dropdown-link-active-bg'     => '#0355ad',
    'dropdown-link-color'         => '#0355ad',
    'font-size-base'              => '1.0rem',
    'h1-font-size'                => '1.8rem',
    'h2-font-size'                => '1.6rem',
    'h3-font-size'                => '1.4rem',
    'h4-font-size'                => '1.2rem',
    'h5-font-size'                => '1.2rem',
    'jumbotron-bg'                => '#f0f0f0',
    'card-cap-bg'                 => '#f0f0f0',
    'card-spacer-y'               => '0.5rem',
    'card-spacer-x'               => '1rem',
    'table-cell-padding-sm'       => '0.2rem',
    'cmln-link-formats'           => "(new: ('color': #f60000, 'hover-color': #6e0000), stub: #0099ff #007aff #10345a underline, extiw: #1b599b none #10345a underline, external: #044eae #ff0000 #2d125d underline)",
];
$egChameleonExternalStyleVariables['link-decoration'] = 'none';
$wgFooterIcons['poweredby']['knowledge.wiki'] = [
    'src' => "$wgScriptPath/KnowledgeWiki.png", 'url' => 'https://fina.oeaw.ac.at',
    'alt' => 'a Knowledge.wiki project', 'height' => 31, 'width' => 100,
];
unset($wgFooterIcons['poweredby']['mediawiki']);
$wgRightsUrl = 'https://creativecommons.org/share-your-work/public-domain/cc0/';
$wgRightsText = 'CC0';
$wgRightsIcon = 'https://licensebuttons.net/p/zero/1.0/88x31.png';

// Sources used by Form:Person, Template:Query Person and Form:Institution.
$finaCountryList = 'publications.europa.eu/resource/cellar/07ed8d46-2b56-11e7-9412-01aa75ed71a1.0001.12/DOC_6';
$wgExternalDataSources['*'] = [
    'allowed urls' => ['https://www.wikidata.org/w/api.php?', 'http://' . $finaCountryList, 'https://' . $finaCountryList],
    'min cache seconds' => 3600,
    'max tries' => 1,
    'options' => [
        'timeout' => 15, 'connectTimeout' => 5, 'followRedirects' => false,
        'userAgent' => 'FINA/1.43 (https://fina.oeaw.ac.at; external-data lookup)',
    ],
];
$wgExternalDataSources['publications.europa.eu'] = [
    'replacements' => ['http://' . $finaCountryList => 'https://' . $finaCountryList],
    'min cache seconds' => 604800,
];
