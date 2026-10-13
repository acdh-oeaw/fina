<?php
$extensions = json_decode(file_get_contents('/opt/fina/extensions.lock.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($extensions as $extension) {
    $directory = '/var/www/html/extensions/' . $extension['directory'];
    if (is_dir($directory)) {
        throw new RuntimeException("Refusing to overwrite $directory");
    }
    mkdir($directory, 0755, true);
    $url = $extension['repository'] . '/archive/' . $extension['commit'] . '.tar.gz';
    $archive = tempnam(sys_get_temp_dir(), 'extension-');
    passthru('curl --fail --silent --show-error --location --retry 3 ' . escapeshellarg($url) . ' --output ' . escapeshellarg($archive), $status);
    if ($status !== 0) {
        throw new RuntimeException("Download failed for $directory");
    }
    passthru('tar -xzf ' . escapeshellarg($archive) . ' --strip-components=1 -C ' . escapeshellarg($directory), $status);
    unlink($archive);
    if ($status !== 0) {
        throw new RuntimeException("Extraction failed for $directory");
    }
}
