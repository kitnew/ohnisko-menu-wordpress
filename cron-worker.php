<?php

/*
 * Ohnisko PDF worker launcher.
 *
 * Intended for Websupport PHP CLI CRON only.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require_once __DIR__ . '/includes/runtime.php';
$runtime = ohnisko_menu_runtime_dir();

$node   = $runtime . '/bin/node';
$worker = $runtime . '/worker.mjs';

$logDir  = $runtime . '/logs';
$logFile = $logDir . '/cron-worker.log';
$lockFile = $runtime . '/worker.lock';

if (!is_dir($logDir)) {
    mkdir($logDir, 0700, true);
}

$lock = fopen($lockFile, 'c');

if ($lock === false) {
    file_put_contents(
        $logFile,
        '[' . date(DATE_ATOM) . "] Unable to open worker lock\n",
        FILE_APPEND
    );

    exit(1);
}

/*
 * Prevent overlapping Chromium processes if one render takes
 * longer than the CRON interval.
 */
if (!flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    exit(0);
}

$pipes = [];

$process = @proc_open(
    [
        $node,
        $worker,
    ],
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
    $runtime
);

if (!is_resource($process)) {
    file_put_contents(
        $logFile,
        '[' . date(DATE_ATOM) . "] Unable to start worker\n",
        FILE_APPEND
    );

    flock($lock, LOCK_UN);
    fclose($lock);

    exit(1);
}

fclose($pipes[0]);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);

$log =
    '[' . date(DATE_ATOM) . '] exit=' . $exitCode . PHP_EOL;

if (trim($stdout) !== '') {
    $log .= trim($stdout) . PHP_EOL;
}

if (trim($stderr) !== '') {
    $log .= "STDERR:\n" . trim($stderr) . PHP_EOL;
}

$log .= PHP_EOL;

file_put_contents(
    $logFile,
    $log,
    FILE_APPEND
);

flock($lock, LOCK_UN);
fclose($lock);

exit($exitCode);
