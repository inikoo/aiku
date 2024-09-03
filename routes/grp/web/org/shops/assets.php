<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Asset\UI\CreateProduct;
use App\Actions\Catalogue\Charge\UI\CreateCharge;
use App\Actions\Catalogue\Charge\UI\EditCharge;
use App\Actions\Catalogue\Charge\UI\IndexCharges;
use App\Actions\Catalogue\Charge\UI\ShowCharge;
use App\Actions\Catalogue\Shipping\UI\CreateShippingZoneSchema;
use App\Actions\Catalogue\Shipping\UI\EditShippingZoneSchema;
use App\Actions\Catalogue\Shipping\UI\IndexShippingZoneSchemas;
use App\Actions\Catalogue\Shipping\UI\ShowShippingZoneSchema;
use App\Actions\UI\Dropshipping\Assets\ShowAssetDashboard;
use App\Stubs\UIDummies\EditDummy;
use App\Stubs\UIDummies\IndexDummies;
use App\Stubs\UIDummies\ShowDummy;
use Illuminate\Support\Facades\Route;

Route::get('', ShowAssetDashboard::class)->name('dashboard');


Route::name("shipping.")->prefix('shipping')
    ->group(function () {
        Route::get('', IndexShippingZoneSchemas::class)->name('index');
        Route::get('create', CreateShippingZoneSchema::class)->name('create');

        Route::prefix('{shippingZoneSchema}')->group(function () {
            Route::get('', ShowShippingZoneSchema::class)->name('show');
            Route::get('edit', EditShippingZoneSchema::class)->name('edit');
        });
    });

Route::name("charges.")->prefix('charges')
    ->group(function () {
        Route::get('', IndexCharges::class)->name('index');
        Route::get('create', CreateCharge::class)->name('create');

        Route::prefix('{charge}')->group(function () {
            Route::get('', ShowCharge::class)->name('show');
            Route::get('edit', EditCharge::class)->name('edit');
        });
    });


Route::name("services.")->prefix('services')
    ->group(function () {
        Route::get('', IndexDummies::class)->name('index');
        Route::get('create', EditDummy::class)->name('create');

        Route::prefix('{service}')->group(function () {
            Route::get('', ShowDummy::class)->name('show');
            Route::get('edit', EditDummy::class)->name('edit');
        });
    });
