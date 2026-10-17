<?php

/*
 * Writes phpunit.ci.xml: phpunit.xml with one "CI" test suite listing every Unit and Feature
 * test file, slowest first by the previous run's JUnit report, so the parallel workers start
 * the long files first instead of finding them last in alphabetical order.
 * Paratest hands files to free workers in the order the suite lists them.
 * Usage (CI): php order-tests-slowest-first.php ../../../../artifacts/last-junit.xml
 * Files the report does not know (new or renamed) go first. Without a report the order is
 * alphabetical, as phpunit.xml would run it.
 */

$durations = [];
$report    = $argv[1] ?? '';
$xml       = is_readable($report) ? @simplexml_load_file($report) : false;

if ($xml !== false) {
    foreach ($xml->xpath('/testsuites/testsuite/testsuite[@file]') ?: [] as $suite) {
        $file             = ltrim((string) $suite['file'], './');
        $durations[$file] = ($durations[$file] ?? 0.0) + (float) $suite['time'];
    }
}

$files = [];
foreach (['tests/Unit', 'tests/Feature'] as $directory) {
    $directoryFiles = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $path) {
        if (str_ends_with($path->getFilename(), 'Test.php')) {
            $directoryFiles[] = $path->getPathname();
        }
    }
    sort($directoryFiles);
    $files = array_merge($files, $directoryFiles);
}

usort($files, fn (string $a, string $b) => ($durations[$b] ?? INF) <=> ($durations[$a] ?? INF));

$config = new DOMDocument();
$config->preserveWhiteSpace = false;
$config->formatOutput       = true;
$config->load('phpunit.xml');

$testSuites = $config->getElementsByTagName('testsuites')->item(0);
while ($testSuites->firstChild) {
    $testSuites->removeChild($testSuites->firstChild);
}

$testSuite = $testSuites->appendChild($config->createElement('testsuite'));
$testSuite->setAttribute('name', 'CI');
foreach ($files as $file) {
    $testSuite->appendChild($config->createElement('file', './'.$file));
}

$config->save('phpunit.ci.xml');

$known = count(array_filter($files, fn (string $file) => isset($durations[$file])));
echo 'order-tests-slowest-first: '.count($files)." files, $known with a previous duration, first: ".implode(', ', array_slice($files, 0, 3))."\n";
