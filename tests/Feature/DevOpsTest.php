<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:04:32 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

$headers = [
    'Authorization' => 'Bearer 1|XptAVXfYLtZKhFoHZHqXKDeTeD1VUyo4A1u1tQIj'
];
$url = 'http://aiku.test/api';

test('create first deployment', function () use ($headers, $url) {
    $response = $this->get($url.'/deployments/create');

    $response->assertOk();
});

test('get latest deployment', function () use ($headers, $url) {
    $result = Http::withHeaders($headers)->get($url . '/deployments/latest');

    expect($result->status())->toEqual(200);
});

test('show deployment', function () use ($headers, $url) {
    $result = Http::withHeaders($headers)->get($url . '/deployments/1');

    expect($result->status())->toEqual(200);
});

test('edit deployment', function () use ($headers, $url) {
    $result = Http::withHeaders($headers)->post($url . '/deployments/latest/edit', [
        "version" => "0.1.1",
        "hash" => "4019599a",
        "state" => "deployed"
    ]);

    expect($result->status())->toEqual(200);
});
