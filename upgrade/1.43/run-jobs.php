<?php
// Drain jobs in fresh, bounded PHP processes. Do not fork the initialized SMW container.
chdir('/var/www/html');
$type = $argv[1] ?? null;
$total = 0;
do {
    $command = [PHP_BINARY, 'maintenance/run.php', 'runJobs', '--maxjobs', '25',
        '--memory-limit', '512M', '--result', 'json'];
    if ($type !== null) {
        $command[] = '--type';
        $command[] = $type;
    }
    $process = proc_open($command, [0 => STDIN, 1 => ['pipe', 'w'], 2 => STDERR], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Could not start the job runner');
    }
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $exit = proc_close($process);
    if ($exit !== 0) {
        fwrite(STDERR, $output);
        throw new RuntimeException("Job runner exited with $exit");
    }
    $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    if (!isset($result['jobs']) || !is_array($result['jobs'])) {
        throw new RuntimeException('Job runner returned an unexpected response');
    }
    foreach ($result['jobs'] as $job) {
        if ($job['status'] !== 'ok') {
            throw new RuntimeException('Job failed: ' . json_encode($job));
        }
    }
    $count = count($result['jobs']);
    $total += $count;
    printf("%s pid=%d type=%s batch=%d total=%d elapsed-ms=%s\n",
        gmdate('c'), getmypid(), $type ?? 'all', $count, $total, $result['elapsed'] ?? '?');
} while ($count > 0);
