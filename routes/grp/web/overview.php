<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 14:12:41 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\PaymentServiceProvider\UI\IndexPaymentServiceProviders;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowOverviewHub::class)->name('hub');
Route::get('/accounting/providers', IndexPaymentServiceProviders::class)->name('accounting.payment-service-providers.index');
