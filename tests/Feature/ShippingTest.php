<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:15:47 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Marketing\ShippingZone\StoreShippingZone;
use App\Actions\Marketing\ShippingZone\UpdateShippingZone;
use App\Actions\Marketing\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Marketing\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Actions\Marketing\Shop\StoreShop;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Marketing\ShippingZone;
use App\Models\Marketing\ShippingZoneSchema;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->paymentAccounts()->count())->toBe(1)
        ->and($shop->outboxes()->count())->toBe(count(OutboxTypeEnum::values()));


    return $shop;
});

test('create shipping zone schema', function ($shop) {
    $shippingZoneSchema = StoreShippingZoneSchema::make()->action($shop, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shop);

    return $shippingZoneSchema;
})->depends('create shop');

test('update shipping zone schema', function ($shippingZoneSchema) {
    $shippingZoneSchema = UpdateShippingZoneSchema::make()->action($shippingZoneSchema, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);
})->depends('create shipping zone schema');

test('create shipping zone', function ($shippingZoneSchema) {
    $shippingZone = StoreShippingZone::make()->action($shippingZoneSchema, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);

    return $shippingZone;
})->depends('create shipping zone schema');

test('update shipping zone', function ($shippingZone) {
    $shippingZone = UpdateShippingZone::make()->action($shippingZone, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZone);
})->depends('create shipping zone');
