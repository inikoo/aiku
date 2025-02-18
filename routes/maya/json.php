<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Feb 2025 12:34:56 Central Indonesia Time,Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\PalletDelivery\Json\ShowPalletDeliveryStatus;
use Illuminate\Support\Facades\Route;

Route::get('pallet-delivery/{palletDelivery:id}/status', ShowPalletDeliveryStatus::class)->name('pallet_delivery.status');
