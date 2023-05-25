<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\ShowStoredItem;
use App\Actions\UI\Fulfilment\FulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', FulfilmentDashboard::class)->name('dashboard');
Route::get('/stored-items', IndexStoredItems::class)->name('stored-items.index');
Route::get('/stored-items/{storedItem}', ShowStoredItem::class)->name('stored-items.show');
