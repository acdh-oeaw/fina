<?php
// Exercise rendering too: siteinfo can succeed while every page parse fails.
$query = http_build_query([
    'action' => 'parse',
    'title' => 'Special:Badtitle/FINA-healthcheck',
    'text' => 'FINA health check: {{CURRENTVERSION}}',
    'contentmodel' => 'wikitext',
    'prop' => 'text',
    'disablelimitreport' => 1,
    'format' => 'json',
    'formatversion' => 2,
]);
$context = stream_context_create(['http' => [
    'timeout' => 3,
    'follow_location' => 0,
    'ignore_errors' => true,
]]);
$body = @file_get_contents(
    'http://127.0.0.1/api.php?' . $query,
    false, $context, 0, 65536
);
$data = json_decode($body === false ? '' : $body, true);
$status = $http_response_header[0] ?? '';
$html = $data['parse']['text'] ?? null;
if (!preg_match('~^HTTP/\S+ 200(?:\s|$)~', $status)
    || !is_string($html)
    || !preg_match('~^FINA health check: 1\.43\.[0-9]+$~D', trim(strip_tags($html)))
    || isset($data['error'])
) {
    fwrite(STDERR, "FINA parser health check failed.\n");
    exit(1);
}
