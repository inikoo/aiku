<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

test('t:seed sends every non-empty translation of a language to the POEditor add endpoint', function () {
    $directory = sys_get_temp_dir().'/aiku-lang-'.bin2hex(random_bytes(6));
    mkdir($directory);
    file_put_contents($directory.'/sk.json', json_encode(['Save' => 'Uložiť', 'Blank' => '', 'Add :count' => 'Pridať: :count']));
    app()->useLangPath($directory);
    config(['translation.api_key' => 'test-key', 'translation.project_id' => 42]);

    Sleep::fake();
    Http::fake(['api.poeditor.com/*' => Http::response(['response' => ['status' => 'success'], 'result' => ['translations' => ['added' => 2]]])]);

    $this->artisan('t:seed', ['locales' => ['sk']])
        ->expectsOutputToContain('sk: 2 translations added to POEditor')
        ->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request) {
        $data = json_decode($request['data'], true);

        return $request->url() === 'https://api.poeditor.com/v2/translations/add'
            && $request['language'] === 'sk'
            && $request['id'] == 42
            && $data === [
                ['term' => 'Save', 'translation' => ['content' => 'Uložiť']],
                ['term' => 'Add :count', 'translation' => ['content' => 'Pridať: :count']],
            ];
    });

    unlink($directory.'/sk.json');
    rmdir($directory);
});

test('t:seed fails when POEditor refuses a batch', function () {
    $directory = sys_get_temp_dir().'/aiku-lang-'.bin2hex(random_bytes(6));
    mkdir($directory);
    file_put_contents($directory.'/sk.json', json_encode(['Save' => 'Uložiť']));
    app()->useLangPath($directory);

    Sleep::fake();
    Http::fake(['api.poeditor.com/*' => Http::response(['response' => ['status' => 'fail', 'message' => 'Too many upload requests']])]);

    $this->artisan('t:seed', ['locales' => ['sk']])
        ->expectsOutputToContain('Too many upload requests')
        ->assertFailed();

    unlink($directory.'/sk.json');
    rmdir($directory);
});
