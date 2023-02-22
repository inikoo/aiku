<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Delivery\DeliveryNote\IndexDeliveryNotes;
use App\Actions\Marketing\Department\IndexDepartments;
use App\Actions\Marketing\Department\ShowDepartment;
use App\Actions\Marketing\Family\IndexFamilies;
use App\Actions\Marketing\Family\ShowFamily;
use App\Actions\Marketing\Product\IndexProducts;
use App\Actions\Marketing\Product\ShowProduct;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Customer\IndexCustomers;
use App\Actions\Sales\Customer\ShowCustomer;
use App\Actions\Sales\Invoice\IndexInvoices;
use App\Actions\Sales\Order\IndexOrders;
use App\Actions\Web\WebUser\IndexWebUser;
use App\Actions\Web\WebUser\CreateWebUser;
use App\Actions\Web\WebUser\ShowWebUser;

Route::get('/', IndexShops::class)->name('index');
Route::get('/{shop}', ShowShop::class)->name('show');

Route::get('/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('show.customers.index');
Route::get('/{shop}/customers/{customer}', [ShowCustomer::class, 'inShop'])->name('show.customers.show');
Route::get('/{shop}/customers/{customer}/web-users', [IndexWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.index');
Route::get('/{shop}/customers/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.show');


Route::get('/{shop:slug}/customers/{customer}/create', [CreateWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.create');

Route::get('/{shop}/departments', [IndexDepartments::class, 'inShop'])->name('show.departments.index');

Route::get('/{shop}/departments/{department}', [ShowDepartment::class, 'inShop'])->name('show.departments.show');

Route::get('/{shop}/departments/{department}/families', [IndexFamilies::class, 'inShopInDepartment'])->name('show.departments.show.families.index');

Route::get('/{shop}/families', [IndexFamilies::class, 'inShop'])->name('show.families.index');

Route::get('/{shop}/families/{family}', [ShowFamily::class, 'inShop'])->name('show.families.show');

Route::get('/{shop}/products', [IndexProducts::class, 'inShop'])->name('show.products.index');

Route::get('/{shop}/products/{product}', [ShowProduct::class, 'inShop'])->name('show.products.show');

Route::get('/{shop}/orders', [IndexOrders::class, 'inShop'])->name('show.orders.index');

Route::get('/{shop}/orders/{order}', [ShowCustomer::class, 'inShop'])->name('show.orders.show');

Route::get('/{shop}/invoices', [IndexInvoices::class, 'inShop'])->name('show.invoices.index');

Route::get('/{shop}/invoices/{invoice}', [ShowCustomer::class, 'inShop'])->name('show.invoices.show');

Route::get('/{shop}/delivery-notes', [IndexDeliveryNotes::class, 'inShop'])->name('show.delivery-notes.index');

Route::get('/{shop}/delivery-notes/{delivery}', [ShowCustomer::class, 'inShop'])->name('show.delivery-notes.show');










