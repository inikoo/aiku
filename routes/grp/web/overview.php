<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 14:12:41 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\PaymentServiceProvider\UI\IndexPaymentServiceProviders;
use App\Actions\Comms\PostRoom\UI\IndexPostRooms;
use App\Actions\Comms\PostRoom\UI\ShowPostRoom;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowOverviewHub::class)->name('hub');
Route::get('/post_rooms', IndexPostRooms::class)->name('post-rooms.index');
Route::get('post-rooms/{postRoom}', ShowPostRoom::class)->name('post-rooms.show');
Route::get('/customers', [IndexCustomers::class, 'inGroup'])->name('customers.index');
Route::get('/customers/{customer}', [ShowCustomer::class, 'inGroup'])->name('customers.show');
// Route::get('/accounting/providers', IndexPaymentServiceProviders::class)->name('accounting.payment-service-providers.index');
