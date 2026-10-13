<?php
// Extend the pinned MediaWiki distribution's dependencies with one shared graph.
// Production-only resolution avoids installing separate, conflicting SMW copies.
$file = '/var/www/html/composer.json';
$config = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
unset($config['require-dev'], $config['autoload-dev']);
$requirements = json_decode(file_get_contents('/opt/fina/requirements.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($requirements as $package => $version) {
    $config['require'][$package] = $version;
}
foreach (glob('/var/www/html/extensions/*/composer.json') as $extension) {
    $extra = json_decode(file_get_contents($extension), true, 512, JSON_THROW_ON_ERROR);
    foreach ($extra['require'] ?? [] as $package => $constraint) {
        if (!isset($config['require'][$package])) {
            $config['require'][$package] = $constraint;
        }
    }
}
$config['config']['audit']['block-insecure'] = true;
$config['config']['allow-plugins'] = [
    'composer/installers' => true,
    'wikimedia/composer-merge-plugin' => true,
];
if (file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n") === false) {
    throw new RuntimeException('Could not write the shared Composer manifest');
}
