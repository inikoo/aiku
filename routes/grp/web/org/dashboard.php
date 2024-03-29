<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:26:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Actions\UI\Grp\Dashboard\ShowDashTV;
use Illuminate\Support\Facades\Route;

Route::get('/tv', ShowDashTV::class)->name('tv');
Route::get('/', ShowOrganisationDashboard::class)->name('show');
