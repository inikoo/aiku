<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 14:51:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Accounting\ShowAccountingDashboard;
use Illuminate\Support\Facades\Route;


Route::get('/', ShowAccountingDashboard::class)->name('dashboard');
