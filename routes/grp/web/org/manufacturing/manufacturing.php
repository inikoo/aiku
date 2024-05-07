<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 19:54:24 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\UI\Manufacturing\ManufacturingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ManufacturingDashboard::class)->name('dashboard');

Route::name('productions.')->prefix('factories')
    ->group(__DIR__."/productions.php");
