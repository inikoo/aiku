<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:50:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentServices;
use Illuminate\Support\Facades\Route;

Route::get('fulfilment/{fulfilment}/services', GetFulfilmentServices::class)->name('pallet-delivery.services.index');
Route::get('fulfilment/{fulfilment}/physical-goods', GetFulfilmentPhysicalGoods::class)->name('pallet-delivery.physical-goods.index');
