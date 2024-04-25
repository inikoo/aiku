<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Helpers\Fetch\FetchTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Helpers\Fetch;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Group;

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

test('can fetch supplier product', function () {

    $command = join(
        ' ',
        [
            'fetch:supplier-products',
            '-s 356'
        ]
    );

    $this->artisan($command)->assertExitCode(0);
    $fetch = Fetch::where('type', FetchTypeEnum::SUPPLIER_PRODUCTS)->first();

    expect($fetch->number_items)->toBe(1)
        ->and($fetch->number_stores)->toBe(1)
        ->and($this->organisation->group->supplyChainStats->number_suppliers)->toBe(1)
        ->and($this->organisation->group->supplyChainStats->number_agents)->toBe(1)
        ->and($this->organisation->group->supplyChainStats->number_agents)->toBe(1);
});


test('can fetch 1 location from aurora', function () {

    $command = join(
        ' ',
        [
            'fetch:locations',
            '-s 9511'
        ]
    );

    $this->artisan($command)->assertExitCode(0);
    $fetch = Fetch::where('type', FetchTypeEnum::LOCATIONS)->first();
    expect($fetch->number_items)->toBe(1)->and($fetch->number_stores)->toBe(1)
        ->and($this->organisation->inventoryStats->number_warehouses)->toBe(1)
        ->and($this->organisation->inventoryStats->number_warehouse_areas)->toBe(1)
        ->and($this->organisation->inventoryStats->number_locations)->toBe(1);
});

test('can fetch 1 stock from aurora', function () {

    $command = join(
        ' ',
        [
            'fetch:stocks',
            '-s 10'
        ]
    );

    $this->artisan($command)->assertExitCode(0);
    $fetch = Fetch::where('type', FetchTypeEnum::STOCKS)->first();
    /** @var Group $group */
    $group= $this->organisation->group;

    expect($fetch->number_items)->toBe(1)
        ->and($fetch->number_stores)->toBe(1)
        ->and($group->inventoryStats->number_stocks)->toBe(1)
        ->and($group->inventoryStats->number_stock_families)->toBe(1);
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
    $fetch = Fetch::where('type', FetchTypeEnum::PROSPECTS)->first();
    expect($fetch->number_items)->toBe(1)->and($fetch->number_stores)->toBe(1);
    $this->artisan($command)->assertExitCode(0);
    $secondFetch = Fetch::where('type', FetchTypeEnum::PROSPECTS)->latest()->first();

    expect($fetchedShop->crmStats->number_prospects)->toBe(1)
        ->and($secondFetch->number_stores)->toBe(0)
        ->and($secondFetch->number_updates)->toBe(0)
        ->and($secondFetch->number_no_changes)->toBe(1);
});

test('can fetch 1 invoice from aurora', function () {

    // $s=10;
    // $s=28;
    $s=450;


    $command = join(
        ' ',
        [
            'fetch:invoices',
            '-s '.$s,
            '-w transactions'
        ]
    );
    $this->artisan($command)->assertExitCode(0);

    $fetch = Fetch::where('type', FetchTypeEnum::INVOICES)->latest()->first();
    expect($fetch->number_items)->toBe(1)
        ->and($fetch->number_errors)->toBe(0)
        ->and($fetch->number_stores)->toBe(1)
        ->and(Invoice::count())->toBe(1);

    $invoice = Invoice::first();

    expect($invoice->shop->stats->number_invoices)->toBe(1);
});

test('can fetch 1 fulfilment invoice from aurora', function () {

    $command = join(
        ' ',
        [
            'fetch:invoices',
            '-s 57130',
            '-w transactions'
        ]
    );
    $this->artisan($command)->assertExitCode(0);

    $fetch = Fetch::where('type', FetchTypeEnum::INVOICES)->latest()->first();
    expect($fetch->number_items)->toBe(1)
        ->and($fetch->number_errors)->toBe(0)
        ->and($fetch->number_stores)->toBe(1)
        ->and(Invoice::count())->toBe(2);

    $invoice = Invoice::first();


    expect($invoice->shop->stats->number_invoices)->toBe(1);
});
