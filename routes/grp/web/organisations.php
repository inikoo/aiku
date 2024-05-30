<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 10 Aug 2022 15:52:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Actions\SysAdmin\Organisation\UI\CreateOrganisation;
use App\Actions\SysAdmin\Organisation\UI\EditOrganisation;
use App\Actions\SysAdmin\Organisation\UI\IndexOrganisations;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisation;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexOrganisations::class)->name('index');
Route::get('/create', CreateOrganisation::class)->name('create');
Route::get('/{organisation}', ShowOrganisation::class)->name('show');
Route::get('edit/{organisation}', EditOrganisation::class)->name('edit');
// Route::get('group/org/{organisation}', ShowOrganisation::class)->name('show');
