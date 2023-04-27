<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 16:51:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipper\UpdateShipper;
use App\Models\Dispatch\Shipper;
use App\Models\Tenancy\Tenant;
use App\Actions\Dispatch\Shipper\StoreShipper;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant->makeCurrent();

});

test('create shipper', function () {
    $shipper = StoreShipper::make()->action(Shipper::factory()->definition());
    $this->assertModelExists($shipper);

    return $shipper;
});

test('update shipper', function ($shipper) {
    $shipper = UpdateShipper::make()->action($shipper, Shipper::factory()->definition());

    $this->assertModelExists($shipper);
})->depends('create shipper');

test('store shipment', function ($shipper) {
    $shipper = StoreShipment::make()->action($shipper, Shipper::factory()->definition());

    $this->assertModelExists($shipper);
})->depends('create shipper');
