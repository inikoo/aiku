<?php

/*
 * Sends the line coverage of a Clover report, totalled per directory, to the aiku DevOps dashboard.
 * Every <line> counts, method lines included, as Codecov counted them, so the history imported from Codecov stays comparable.
 * Usage (CI): php report-coverage.php clover.xml
 * Needs GITHUB_RUN_ID, GITHUB_SHA and DEVOPS_TOKEN; COVERAGE_URL overrides the endpoint.
 * Never fails the build: any problem is printed and it exits 0.
 */

$report = $argv[1] ?? 'clover.xml';
$runId  = getenv('GITHUB_RUN_ID');
$token  = getenv('DEVOPS_TOKEN');

if (!$runId || !$token) {
    fwrite(STDERR, "report-coverage: no GITHUB_RUN_ID or DEVOPS_TOKEN, skipping\n");
    exit(0);
}

$xml = is_readable($report) ? @simplexml_load_file($report) : false;
if ($xml === false) {
    fwrite(STDERR, "report-coverage: $report missing or unreadable, skipping\n");
    exit(0);
}

$root        = rtrim(getcwd(), '/').'/';
$directories = [];
foreach ($xml->xpath('//file') ?: [] as $file) {
    $directory                  = dirname(str_starts_with((string) $file['name'], $root) ? substr((string) $file['name'], strlen($root)) : (string) $file['name']);
    $lines                      = $file->xpath('line') ?: [];
    $directories[$directory][0] = ($directories[$directory][0] ?? 0) + count($lines);
    $directories[$directory][1] = ($directories[$directory][1] ?? 0) + count(array_filter($lines, fn (SimpleXMLElement $line) => (int) $line['count'] > 0));
}

$response = @file_get_contents(getenv('COVERAGE_URL') ?: 'https://aiku.io/devops/coverage', false, stream_context_create([
    'http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json\r\nAccept: application/json\r\nX-DEVOPS-TOKEN: $token\r\n",
        'content'       => json_encode(['run_id' => (int) $runId, 'head_sha' => getenv('GITHUB_SHA') ?: null, 'directories' => $directories]),
        'timeout'       => 30,
        'ignore_errors' => true,
    ],
]));

echo 'report-coverage: '.($http_response_header[0] ?? 'no response').' '.$response."\n";
exit(0);
