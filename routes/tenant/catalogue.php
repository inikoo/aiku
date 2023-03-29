<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 Mar 2023 11:30:06 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\UI\Catalogue\CatalogueHub;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogueHub::class,'inShop'])->name('hub');
