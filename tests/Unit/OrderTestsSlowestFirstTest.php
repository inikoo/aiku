<?php

it('lists test files slowest first by the previous report, unknown files first, Unit before Feature without one', function () {
    $script    = __DIR__.'/../../devops/ci/order-tests-slowest-first.php';
    $directory = sys_get_temp_dir().'/aiku-order-'.bin2hex(random_bytes(6));
    foreach (['tests/Unit', 'tests/Feature/Sub'] as $folder) {
        mkdir("$directory/$folder", 0777, true);
    }
    foreach (['tests/Unit/AUnitTest.php', 'tests/Feature/FastTest.php', 'tests/Feature/SlowTest.php', 'tests/Feature/Sub/NewTest.php', 'tests/Feature/Helper.php'] as $file) {
        touch("$directory/$file");
    }
    file_put_contents("$directory/phpunit.xml", '<?xml version="1.0"?><phpunit><testsuites><testsuite name="Unit"><directory>./tests/Unit</directory></testsuite></testsuites><php><env name="APP_ENV" value="testing"/></php></phpunit>');
    file_put_contents("$directory/last-junit.xml", '<?xml version="1.0"?><testsuites><testsuite name="" time="12"><testsuite name="a" file="tests/Unit/AUnitTest.php" time="1"/><testsuite name="b" file="tests/Feature/FastTest.php" time="2"/><testsuite name="c" file="tests/Feature/SlowTest.php" time="9"/></testsuite></testsuites>');

    $listedFiles = function (string $report) use ($script, $directory): array {
        exec('cd '.escapeshellarg($directory).' && '.PHP_BINARY.' '.escapeshellarg($script).' '.escapeshellarg($report));
        $config = simplexml_load_file("$directory/phpunit.ci.xml");

        return [array_map('strval', $config->xpath('//testsuite[@name="CI"]/file')), count($config->xpath('//testsuite')), (string) $config->php->env['name']];
    };

    expect($listedFiles('last-junit.xml'))->toBe([['./tests/Feature/Sub/NewTest.php', './tests/Feature/SlowTest.php', './tests/Feature/FastTest.php', './tests/Unit/AUnitTest.php'], 1, 'APP_ENV'])
        ->and($listedFiles('missing.xml')[0])->toBe(['./tests/Unit/AUnitTest.php', './tests/Feature/FastTest.php', './tests/Feature/SlowTest.php', './tests/Feature/Sub/NewTest.php']);

    exec('rm -rf '.escapeshellarg($directory));
});
