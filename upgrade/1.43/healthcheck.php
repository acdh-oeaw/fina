<?php
// Check Apache and MediaWiki together: PHP fatals can otherwise return HTTP 200.
$context = stream_context_create(['http' => [
    'timeout' => 3,
    'follow_location' => 0,
    'ignore_errors' => true,
]]);
$body = @file_get_contents(
    'http://127.0.0.1/api.php?action=query&meta=siteinfo&format=json',
    false, $context, 0, 65536
);
$data = json_decode($body === false ? '' : $body, true);
$status = $http_response_header[0] ?? '';
$generator = $data['query']['general']['generator'] ?? null;
if (!preg_match('~^HTTP/\S+ 200(?:\s|$)~', $status)
    || !is_string($generator)
    || !str_starts_with($generator, 'MediaWiki 1.43.')
    || isset($data['error'])
) {
    fwrite(STDERR, "FINA API health check failed.\n");
    exit(1);
}
