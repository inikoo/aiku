<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */



use App\Actions\Web\Website\IndexWebsites;
use App\Actions\Web\Website\ShowWebsite;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexWebsites::class)->name('index');
Route::get('/{website}', ShowWebsite::class)->name('show');
