<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 02 Jun 2023 14:30:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\Dropshipping\DropshippingDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', DropshippingDashboard::class)->name('dashboard');
