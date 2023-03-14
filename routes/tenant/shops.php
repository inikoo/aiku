<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\Dispatch\DeliveryNote\ShowDeliveryNote;
use App\Actions\Marketing\Department\UI\CreateDepartment;
use App\Actions\Marketing\Department\UI\EditDepartment;
use App\Actions\Marketing\Department\UI\IndexDepartments;
use App\Actions\Marketing\Department\UI\ShowDepartment;
use App\Actions\Marketing\Family\IndexFamilies;
use App\Actions\Marketing\Family\ShowFamily;
use App\Actions\Marketing\Product\UI\CreateProduct;
use App\Actions\Marketing\Product\UI\EditProduct;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\Product\UI\ShowProduct;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Customer\UI\CreateCustomer;
use App\Actions\Sales\Customer\UI\EditCustomer;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Actions\Sales\Customer\UI\ShowCustomer;
use App\Actions\Sales\Invoice\IndexInvoices;
use App\Actions\Sales\Invoice\ShowInvoice;
use App\Actions\Sales\Order\IndexOrders;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\Web\Website\IndexWebsites;
use App\Actions\Web\Website\ShowWebsite;
use App\Actions\Web\WebUser\CreateWebUser;
use App\Actions\Web\WebUser\IndexWebUser;
use App\Actions\Web\WebUser\ShowWebUser;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexShops::class)->name('index');
Route::get('/{shop}', ShowShop::class)->name('show');

Route::get('/{shop}/customers', [IndexCustomers::class, 'inShop'])->name('show.customers.index');
Route::get('/{shop}/customers/create', CreateCustomer::class)->name('show.customers.create');

Route::get('/{shop}/customers/{customer}', [ShowCustomer::class, 'inShop'])->name('show.customers.show');
Route::get('/{shop}/customers/{customer}/edit', [EditCustomer::class, 'inShop'])->name('show.customers.edit');

Route::get('/{shop}/customers/{customer}/web-users', [IndexWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.index');
Route::get('/{shop}/customers/{customer}/web-users/{webUser}', [ShowWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.show');


Route::get('/{shop}/customers/{customer}/web-users/create', [CreateWebUser::class, 'inShopInCustomer'])->name('show.customers.show.web-users.create');

Route::get('/{shop}/departments', [IndexDepartments::class, 'inShop'])->name('show.departments.index');
Route::get('/{shop}/departments/create', CreateDepartment::class)->name('show.departments.create');
Route::get('/{shop}/departments/{department}', [ShowDepartment::class, 'inShop'])->name('show.departments.show');
Route::get('/{shop}/departments/{department}/edit', [EditDepartment::class, 'inShop'])->name('show.departments.edit');


Route::get('/{shop}/departments/{department}/families', [IndexFamilies::class, 'inShopInDepartment'])->name('show.departments.show.families.index');

Route::get('/{shop}/departments/{department}/products', [IndexProducts::class, 'inShopInDepartment'])->name('show.departments.show.products.index');

Route::get('/{shop}/families', [IndexFamilies::class, 'inShop'])->name('show.families.index');

Route::get('/{shop}/families/{family}', [ShowFamily::class, 'inShop'])->name('show.families.show');

Route::get('/{shop}/products', [IndexProducts::class, 'inShop'])->name('show.products.index');
Route::get('/{shop}/products/create', CreateProduct::class)->name('show.products.create');
Route::get('/{shop}/products/{product}', [ShowProduct::class, 'inShop'])->name('show.products.show');
Route::get('/{shop}/products/{product}/edit', [EditProduct::class, 'inShop'])->name('show.products.edit');


Route::get('/{shop}/orders', [IndexOrders::class, 'inShop'])->name('show.orders.index');

Route::get('/{shop}/orders/{order}', [ShowOrder::class, 'inShop'])->name('show.orders.show');

Route::get('/{shop}/invoices', [IndexInvoices::class, 'inShop'])->name('show.invoices.index');

Route::get('/{shop}/invoices/{invoice}', [ShowInvoice::class, 'inShop'])->name('show.invoices.show');

Route::get('/{shop}/delivery-notes', [IndexDeliveryNotes::class, 'inShop'])->name('show.delivery-notes.index');

Route::get('/{shop}/delivery-notes/{delivery}', [ShowDeliveryNote::class, 'inShop'])->name('show.delivery-notes.show');

Route::get('/{shop}/websites', [IndexWebsites::class, 'inShop'])->name('show.websites.index');
Route::get('/{shop}/websites/{website}', [ShowWebsite::class, 'inShop'])->name('show.websites.show');
