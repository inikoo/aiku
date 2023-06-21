<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:43:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Prospect\IndexProspects;
use App\Actions\UI\CRM\CRMDashboard;

Route::get('/', [CRMDashboard::class,'inTenant'])->name('dashboard');
Route::get('/customers', [IndexCustomers::class, 'inTenant'])->name('customers.index');
Route::get('/prospects', [IndexProspects::class, 'inTenant'])->name('prospects.index');


Route::get('/{shop}', [CRMDashboard::class,'inShop'])->name('shops.show.dashboard');

Route::get('/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('shops.show.customers.index');

Route::get('/{shop}/prospects', [IndexProspects::class, 'inShop'])->name('shops.show.prospects.index');
