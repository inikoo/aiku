<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:26:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\Dashboard\DashTV;
use Illuminate\Support\Facades\Route;

Route::get('/tv', DashTV::class)->name('tv');
Route::get('/', Dashboard::class)->name('show');
