<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Models\Helpers\Fetch;
use App\Models\Market\Shop;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();
    $this->organisation->update(
        [
            'source' => [
                'type'    => 'Aurora',
                'db_name' => config('database.connections.aurora.database'),
            ]
        ]
    );
});

test('can fetch 1 prospect from aurora', function () {


    $command = join(
        ' ',
        [
            'fetch:prospects',
            '-s 2'
        ]
    );

    $this->artisan($command)->assertExitCode(0);
    /** @var Shop $fetchedShop */
    $fetchedShop=Shop::whereNotNull('source_id')->first();
    expect($fetchedShop)->toBeInstanceOf(Shop::class)->and($fetchedShop->source_id)->toBe('1:2')
        ->and($fetchedShop->crmStats->number_prospects)->toBe(1);
    $fetch = Fetch::first();
    expect($fetch->number_items)->toBe(1)->and($fetch->number_stores)->toBe(1);
    $this->artisan($command)->assertExitCode(0);
    $secondFetch = Fetch::find(2);


    expect($fetchedShop->crmStats->number_prospects)->toBe(1)
        ->and($secondFetch->number_stores)->toBe(0)
        ->and($secondFetch->number_updates)->toBe(0)
        ->and($secondFetch->number_no_changes)->toBe(1);
});

test('can fetch 1 invoice from aurora', function () {

    $this->organisation->update(
        [
            'source' => [
                'type'    => 'Aurora',
                'db_name' => config('database.connections.aurora.database'),
            ]
        ]
    );

    $command = join(
        ' ',
        [
            'fetch:invoices',
            $this->organisation->slug,
            '-s 10'
        ]
    );

    $this->artisan($command)->assertExitCode(0);
    $this->shop->refresh();
    expect($this->shop->stats->number_invoices)->toBe(3);


})->todo();

