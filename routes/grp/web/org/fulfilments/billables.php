<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:07:52 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Product\UI\CreatePhysicalGoods;
use App\Actions\Catalogue\Product\UI\EditPhysicalGoods;
use App\Actions\Catalogue\Product\UI\ShowPhysicalGoods;
use App\Actions\Catalogue\Product\UI\ShowProduct;
use App\Actions\Catalogue\Service\UI\CreateService;
use App\Actions\Catalogue\Service\UI\EditService;
use App\Actions\Catalogue\Service\UI\ShowService;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentAssets;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentRentals;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentServices;
use App\Actions\Fulfilment\PalletDelivery\GetDeliveryPhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\GetDeliveryServices;
use App\Actions\Fulfilment\PalletReturn\GetReturnPhysicalGoods;
use App\Actions\Fulfilment\PalletReturn\GetReturnServices;
use App\Actions\Fulfilment\Rental\UI\CreateRental;
use App\Actions\Fulfilment\Rental\UI\EditRental;
use App\Actions\Fulfilment\Rental\UI\ShowRental;
use App\Stubs\UIDummies\IndexDummies;

Route::get('billables', IndexFulfilmentAssets::class)->name('index');
Route::get('billables/{product}', [ShowProduct::class, 'inFulfilment'])->name('show');

Route::get('rentals', IndexFulfilmentRentals::class)->name('rentals.index');
Route::get('rentals/create', CreateRental::class)->name('rentals.create');
Route::get('rentals/{rental}', [ShowRental::class, 'inFulfilment'])->name('rentals.show');
Route::get('rentals/{rental}/edit', [EditRental::class, 'inFulfilment'])->name('rentals.edit');

Route::get('pallet-delivery/{palletDelivery}/services', GetDeliveryServices::class)->name('pallet-delivery.services.index');
Route::get('pallet-return/{palletDelivery}/services', GetReturnServices::class)->name('pallet-return.services.index');
Route::get('services', IndexFulfilmentServices::class)->name('services.index');
Route::get('services/create', CreateService::class)->name('services.create');
Route::get('services/{service}', [ShowService::class, 'inFulfilment'])->name('services.show');
Route::get('services/{service}/edit', [EditService::class, 'inFulfilment'])->name('services.edit');



Route::get('pallet-delivery/{palletDelivery}/physical-goods', GetDeliveryPhysicalGoods::class)->name('pallet-delivery.physical-goods.index');
Route::get('pallet-return/{palletDelivery}/physical-goods', GetReturnPhysicalGoods::class)->name('pallet-return.physical-goods.index');
Route::get('physical-goods', IndexFulfilmentPhysicalGoods::class)->name('outers.index');
Route::get('physical-goods/create', CreatePhysicalGoods::class)->name('outers.create');
Route::get('physical-goods/{product}', [ShowPhysicalGoods::class, 'inFulfilment'])->name('outers.show');
Route::get('physical-goods/{product}/edit', [EditPhysicalGoods::class, 'inFulfilment'])->name('outers.edit');

Route::get('shipping', IndexDummies::class)->name('shipping.index');
