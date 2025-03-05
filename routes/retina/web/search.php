<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Mar 2025 00:37:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Helpers\RetinaSearch\UI\IndexRetinalSearch;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexRetinalSearch::class)->name('index');
