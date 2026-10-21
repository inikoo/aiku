<?php

/*
 * Sends the size of each built frontend bundle (public/<bundle>) to the aiku DevOps dashboard.
 * Usage (CI, after npm run build): php report-bundle-sizes.php grp aiku-public iris retina pupil chat-widget
 * Needs GITHUB_RUN_ID, GITHUB_SHA and DEVOPS_TOKEN; BUNDLE_SIZES_URL overrides the endpoint.
 * Never fails the build: any problem is printed and it exits 0.
 */

$runId = getenv('GITHUB_RUN_ID');
$token = getenv('DEVOPS_TOKEN');

if (!$runId || !$token) {
    fwrite(STDERR, "report-bundle-sizes: no GITHUB_RUN_ID or DEVOPS_TOKEN, skipping\n");
    exit(0);
}

$bundles = [];
foreach (array_slice($argv, 1) as $name) {
    $directory = "public/$name";
    if (!is_dir($directory)) {
        continue;
    }

    $bundle = ['files' => 0, 'js_bytes' => 0, 'css_bytes' => 0, 'gzip_bytes' => 0, 'largest' => []];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
        $extension = $file->getExtension();
        if (!in_array($extension, ['js', 'css'], true)) {
            continue;
        }
        $bytes     = $file->getSize();
        $gzipBytes = strlen(gzencode(file_get_contents($file->getPathname()), 9));

        $bundle['files']++;
        $bundle[$extension.'_bytes'] += $bytes;
        $bundle['gzip_bytes']        += $gzipBytes;
        $bundle['largest'][]          = ['file' => substr($file->getPathname(), strlen($directory) + 1), 'bytes' => $bytes, 'gzip_bytes' => $gzipBytes];
    }

    usort($bundle['largest'], fn (array $a, array $b) => $b['bytes'] <=> $a['bytes']);
    $bundle['largest'] = array_slice($bundle['largest'], 0, 10);
    $bundles[$name]    = $bundle;
}

if (!$bundles) {
    fwrite(STDERR, "report-bundle-sizes: no built bundles found, skipping\n");
    exit(0);
}

$response = @file_get_contents(getenv('BUNDLE_SIZES_URL') ?: 'https://aiku.io/devops/bundle-sizes', false, stream_context_create([
    'http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json\r\nAccept: application/json\r\nX-DEVOPS-TOKEN: $token\r\n",
        'content'       => json_encode(['run_id' => (int) $runId, 'head_sha' => getenv('GITHUB_SHA') ?: null, 'bundles' => $bundles]),
        'timeout'       => 30,
        'ignore_errors' => true,
    ],
]));

echo 'report-bundle-sizes: '.($http_response_header[0] ?? 'no response').' '.json_encode(array_map(fn (array $bundle) => $bundle['js_bytes'] + $bundle['css_bytes'], $bundles))."\n";
exit(0);
