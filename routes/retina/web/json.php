<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 12:02:06 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Retina\Storage\PalletDelivery\Json\GetRetinaFulfilmentPhysicalGoods;
use App\Actions\Retina\Storage\PalletDelivery\Json\GetRetinaFulfilmentServices;
use App\Actions\Retina\Storage\PalletReturn\Json\GetRetinaReturnPallets;
use App\Actions\Retina\Storage\PalletReturn\Json\GetRetinaReturnStoredItems;
use Illuminate\Support\Facades\Route;

Route::get('fulfilment/{fulfilment}/delivery/{scope}/services', [GetRetinaFulfilmentServices::class, 'inPalletDelivery'])->name('fulfilment.delivery.services.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/services', [GetRetinaFulfilmentServices::class, 'inPalletReturn'])->name('fulfilment.return.services.index');


Route::get('fulfilment/{fulfilment}/delivery/{scope}/physical-goods', [GetRetinaFulfilmentPhysicalGoods::class, 'inPalletDelivery'])->name('fulfilment.delivery.physical-goods.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/physical-goods', [GetRetinaFulfilmentPhysicalGoods::class, 'inPalletReturn'])->name('fulfilment.return.physical-goods.index');

Route::get('fulfilment/return/pallets', GetRetinaReturnPallets::class)->name('fulfilment.return.pallets');

Route::get('fulfilment/{fulfilment}/return/{scope}/stored-items', GetRetinaReturnStoredItems::class)->name('fulfilment.return.stored-items');
