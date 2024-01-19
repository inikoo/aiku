<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:26:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\SysAdmin\Organisation\UI\IndexOrganisation;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\Dashboard\ShowDashTV;
use Illuminate\Support\Facades\Route;

Route::get('/tv', ShowDashTV::class)->name('tv');
Route::get('/', ShowDashboard::class)->name('show');
