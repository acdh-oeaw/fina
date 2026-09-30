<?php
// Temporary exception for known advisories on the pinned legacy SMW release.
// This does not fix its XSS issues. Audit must continue reporting them.
$version = getenv( 'SMW_VERSION' );
if ( $version !== '4.2.0' ) {
    throw new RuntimeException( 'Review/remove legacy SMW exceptions before changing SMW_VERSION.' );
}
if ( is_file( 'composer.lock' ) ) {
    throw new RuntimeException( 'Review the SRF lock file before changing its SMW source.' );
}
$package = json_decode( file_get_contents( 'composer.json' ), false, 512, JSON_THROW_ON_ERROR );
if ( $package->name !== 'mediawiki/semantic-result-formats' ) {
    throw new RuntimeException( 'Run this preparation only in SemanticResultFormats.' );
}
$package->require->{'mediawiki/semantic-media-wiki'} = $version;
// Use the already installed and locally patched SMW. Keep a valid symlink in
// SRF's installer directory instead of deleting files referenced by autoload.
$package->repositories = [ (object)[
    'type' => 'path',
    'url' => '../SemanticMediaWiki',
    'options' => (object)[
        'symlink' => true,
        'versions' => (object)[ 'mediawiki/semantic-media-wiki' => $version ]
    ]
] ];
$package->config ??= new stdClass();
$package->config->policy ??= new stdClass();
$package->config->policy->advisories ??= new stdClass();
$package->config->policy->advisories->block = true;
$exceptions = new stdClass();
foreach ( [
    'PKSA-8k3f-637m-ptt7', 'PKSA-rt5h-3dwq-vdnx',
    'PKSA-5kcn-7vbr-1pdw', 'PKSA-k3v8-z9j2-2f38',
    'PKSA-stqx-crkb-215f', 'PKSA-2trz-n7bz-xg2h',
    'PKSA-6xyn-p8dg-1kgj', 'PKSA-q8ty-xz99-jhhj'
] as $id ) {
    $exceptions->{$id} = (object)[
        'on-audit' => false,
        'reason' => 'Temporary MW 1.39 emergency image with pinned SMW 4.2.0; smwtask disabled, other known issues remain. Upgrade required.'
    ];
}
$package->config->policy->advisories->{'ignore-id'} = $exceptions;
if ( file_put_contents( 'composer.json', json_encode( $package,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) . "\n" ) === false ) {
    throw new RuntimeException( 'Cannot write SRF composer.json.' );
}
