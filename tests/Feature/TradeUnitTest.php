<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:16:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Models\Goods\TradeUnit;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);

    return $tradeUnit;
});

test('update trade unit', function ($tradeUnit) {
    $tradeUnit = UpdateTradeUnit::make()->action($tradeUnit, TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);
})->depends('create trade unit');
