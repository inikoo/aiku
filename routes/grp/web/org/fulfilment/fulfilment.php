<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 14:54:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\Fulfilment\UI\CreateFulfilment;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilments;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\IndexFulfilmentCustomers;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\FulfilmentOrder\UI\ShowfulfilmentOrder;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\StoredItem\SetDamagedStoredItem;
use App\Actions\Fulfilment\StoredItem\SetReturnStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\CreateStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\EditStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\ShowStoredItem;
use App\Actions\UI\Fulfilment\FulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::prefix('shops')->name('shops.')
    ->group(function () {
        Route::get('', IndexFulfilments::class)->name('index');
        Route::get('create', CreateFulfilment::class)->name('create');


        Route::prefix('{fulfilment}')
            ->group(function () {
                Route::get('', ShowFulfilment::class)->name('show');

                Route::prefix("crm")
                    ->name("crm.")
                    ->group(__DIR__."/crm.php");

                Route::get('/pallets', IndexPallets::class)->name('stored-items.index');
            });

    });






/*

Route::get('/', FulfilmentDashboard::class)->name('dashboard');
Route::get('/stored-items', IndexStoredItems::class)->name('stored-items.index');
Route::get('/customers/{customer}/stored-items/create', CreateStoredItem::class)->name('stored-items.create');
Route::get('stored-items/{storedItem}', ShowStoredItem::class)->name('stored-items.show');
Route::get('stored-items/{storedItem}/edit', EditStoredItem::class)->name('stored-items.edit');
Route::get('stored-items/{storedItem}/damaged', SetDamagedStoredItem::class)->name('stored-items.setDamaged');
Route::get('stored-items/{storedItem}/return', SetReturnStoredItem::class)->name('stored-items.setReturn');

Route::get('/customers', [IndexFulfilmentCustomers::class, 'inShop'])->name('customers.index');
Route::get('/customers/{customer:slug}', ShowFulfilmentCustomer::class)->name('customers.show');

Route::get('/orders', [IndexFulfilmentOrders::class, 'inShop'])->name('orders.index');
Route::get('/orders/{order}', ShowFulfilmentOrder::class)->name('orders.show');
*/
