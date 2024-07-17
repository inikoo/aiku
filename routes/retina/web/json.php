<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 12:02:06 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\Json\GetFulfilmentServices;
use App\Actions\UI\Retina\Dashboard\ShowDashboard;
use Illuminate\Support\Facades\Route;

Route::get('fulfilment/{fulfilment}/services', GetFulfilmentServices::class)->name('fulfilment.services.index');
Route::get('fulfilment/{fulfilment}/physical-goods', GetFulfilmentPhysicalGoods::class)->name('fulfilment.physical-goods.index');

