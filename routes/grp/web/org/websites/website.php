<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Jun 2024 17:50:38 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Web\Website\UI\IndexWebsites;
use Illuminate\Support\Facades\Route;

Route::get('', IndexWebsites::class)->name('index');
