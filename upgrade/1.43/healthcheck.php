<?php
// Check MediaWiki and a bounded database query without invoking the parser.
$query = http_build_query([
    'action' => 'query',
    'meta' => 'siteinfo',
    'siprop' => 'general',
    'list' => 'allpages',
    'aplimit' => 1,
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
$generator = $data['query']['general']['generator'] ?? null;
$pages = $data['query']['allpages'] ?? null;
if (!preg_match('~^HTTP/\S+ 200(?:\s|$)~', $status)
    || !is_string($generator)
    || !preg_match('~^MediaWiki 1\.43\.[0-9]+$~D', $generator)
    || !is_array($pages)
    || isset($data['error'])
) {
    fwrite(STDERR, "FINA MediaWiki/API/database health check failed.\n");
    exit(1);
}
