<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 1 Dec 2022, 15:45, Plane Bali-KL
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\ShowFulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowFulfilmentDashboard::class)->name('dashboard');

