<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 12:02:06 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentServices;
use Illuminate\Support\Facades\Route;

Route::get('fulfilment/{fulfilment}/delivery/{scope}/services', [GetFulfilmentServices::class, 'inPalletDelivery'])->name('fulfilment.delivery.services.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/services', [GetFulfilmentServices::class, 'inPalletReturn'])->name('fulfilment.return.services.index');

Route::get('fulfilment/{fulfilment}/delivery/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inPalletDelivery'])->name('fulfilment.delivery.physical-goods.index');
Route::get('fulfilment/{fulfilment}/return/{scope}/physical-goods', [GetFulfilmentPhysicalGoods::class, 'inPalletReturn'])->name('fulfilment.return.physical-goods.index');
