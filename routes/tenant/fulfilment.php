<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\Fulfilment\FulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', FulfilmentDashboard::class)->name('dashboard');
