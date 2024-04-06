<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jan 2024 03:24:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\UI\Reports\IndexReports;

Route::get('/', IndexReports::class)->name('index');
