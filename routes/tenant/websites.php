<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:46:44 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Web\Webpage\IndexWebpages;
use App\Actions\Web\Website\IndexWebsites;
use App\Actions\Web\Website\WebsiteHub;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexWebsites::class)->name('index');
Route::get('/{website}', WebsiteHub::class)->name('show');
Route::get('/{website}/webpage', IndexWebpages::class)->name('show.webpages.index');
