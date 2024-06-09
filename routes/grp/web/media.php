<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 18:54:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Helpers\Media\ShowMedia;
use Illuminate\Support\Facades\Route;

Route::get('/{media}', ShowMedia::class)->name('show');
