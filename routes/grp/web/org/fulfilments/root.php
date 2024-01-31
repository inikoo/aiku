<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 22:13:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Fulfilment\UI\CreateFulfilment;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilments;
use App\Actions\Fulfilment\Pallet\UI\CreatePallet;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowPalletDelivery;
use Illuminate\Support\Facades\Route;

Route::get('', IndexFulfilments::class)->name('index');
Route::get('create', CreateFulfilment::class)->name('create');


Route::prefix('{fulfilment}')->name('show.')
    ->group(function () {



        Route::name("catalogue.")
            ->group(__DIR__."/catalogue.php");

        Route::name("crm.")
            ->group(__DIR__."/crm.php");

        //Route::get('/pallets', IndexPallets::class)->name('.pallets.index');
        //Route::get('/pallets/create', CreatePallet::class)->name('.pallets.create');
        //Route::get('deliveries', IndexPalletDeliveries::class)->name('.pallet-deliveries.index');
        //Route::get('deliveries/{palletDelivery}', ShowPalletDelivery::class)->name('.pallet-deliveries.show');

        Route::prefix("websites")
            ->name("web.websites.")
            ->group(__DIR__."/websites.php");

    });
