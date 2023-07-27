<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\FulfilmentCustomer\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\FulfilmentOrder\UI\ShowfulfilmentOrder;
use App\Actions\Fulfilment\StoredItem\UI\CreateStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\EditStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\ShowStoredItem;
use App\Actions\UI\Fulfilment\FulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', FulfilmentDashboard::class)->name('dashboard');
Route::get('/stored-items', IndexStoredItems::class)->name('stored-items.index');
Route::get('/customers/{customer}/stored-items/create', CreateStoredItem::class)->name('stored-items.create');
Route::get('stored-items/{storedItem}', ShowStoredItem::class)->name('stored-items.show');
Route::get('stored-items/{storedItem}/edit', EditStoredItem::class)->name('stored-items.edit');

Route::get('/customers', [IndexFulfilmentCustomers::class, 'inShop'])->name('customers.index');
Route::get('/customers/{customer:slug}', ShowFulfilmentCustomer::class)->name('customers.show');

Route::get('/orders', [IndexFulfilmentOrders::class, 'inShop'])->name('orders.index');
Route::get('/orders/{order}', ShowFulfilmentOrder::class)->name('orders.show');
