<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:43:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\Leads\Prospect\IndexProspects;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Actions\Sales\Order\UI\IndexOrders;
use App\Actions\UI\CRM\CRMDashboard;

Route::get('/', [CRMDashboard::class,'inTenant'])->name('dashboard');
Route::get('/customers', [IndexCustomers::class, 'inTenant'])->name('customers.index');
Route::get('/orders', [IndexOrders::class, 'inTenant'])->name('orders.index');


Route::get('/{shop}', [CRMDashboard::class,'inShop'])->name('shops.show.dashboard');

Route::get('/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('shops.show.customers.index');
Route::get('/{shop}/orders', [IndexOrders::class, 'inShop'])->name('shops.show.orders.index');
Route::get('/{shop}/invoices', [IndexInvoices::class, 'inShop'])->name('shops.show.invoices.index');
Route::get('/{shop}/delivery-notes', [IndexDeliveryNotes::class, 'inShop'])->name('shops.show.delivery-notes.index');

Route::get('/{shop}/prospects', [IndexProspects::class, 'inShop'])->name('shops.show.prospects.index');
