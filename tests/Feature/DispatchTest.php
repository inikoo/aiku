<?php

namespace Tests\Feature;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipper\StoreShipper;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\Shipper;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shipper', function () {
    $shiper = StoreShipper::make()->action(Shipper::factory()->definition());
    $this->assertModelExists($shiper);
    return $shiper;
});
