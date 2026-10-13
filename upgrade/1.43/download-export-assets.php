<?php
// Install browser distributions and their licenses from integrity-locked npm archives.
$assets = json_decode(file_get_contents('/opt/fina/export-assets.lock.json'), true, 512, JSON_THROW_ON_ERROR);
$destination = '/var/www/html/extensions/Kma/resources/vendor';
if (!is_dir($destination)) {
    mkdir($destination, 0755, true);
}
foreach ($assets as $asset) {
    $archive = tempnam(sys_get_temp_dir(), 'fina-export-');
    try {
        passthru('curl --fail --silent --show-error --location --retry 3 ' . escapeshellarg($asset['url']) . ' --output ' . escapeshellarg($archive), $status);
        $integrity = 'sha512-' . base64_encode(hash_file('sha512', $archive, true));
        if ($status !== 0 || !hash_equals($asset['integrity'], $integrity)) {
            throw new RuntimeException('Download or integrity check failed: ' . $asset['package']);
        }
        foreach ($asset['files'] as $source => $target) {
            $process = proc_open('tar -xOzf ' . escapeshellarg($archive) . ' ' . escapeshellarg('package/' . $source), [
                0 => ['file', '/dev/null', 'r'],
                1 => ['file', $destination . '/' . $target, 'w'],
                2 => STDERR,
            ], $pipes);
            if (!is_resource($process) || proc_close($process) !== 0) {
                throw new RuntimeException('Could not extract: ' . $source);
            }
        }
    } finally {
        unlink($archive);
    }
}
