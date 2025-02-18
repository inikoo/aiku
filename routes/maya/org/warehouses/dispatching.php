<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 12:33:52 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\Dispatching\DeliveryNote\UI\ShowDeliveryNote;
use App\Actions\Dispatching\Picking\UI\IndexPickings;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInReturn;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\PalletReturn\UI\ShowStoredItemReturn;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItemsInReturn;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDispatchHub::class)->name('backlog');
Route::get('/delivery-notes', IndexDeliveryNotes::class)->name('delivery-notes');
Route::get('/delivery-notes/{deliveryNote:id}', [ShowDeliveryNote::class, 'inWarehouse'])->name('delivery-notes.show')->withoutScopedBindings();
Route::get('delivery-notes/{deliveryNote:id}/items', IndexPickings::class)->name('delivery-notes.items.index');

Route::get('handling-fulfilment-returns', [IndexPalletReturns::class, 'inWarehouseHandling'])->name('pallet_returns.handling.index');
Route::get('dispatched-fulfilment-returns', [IndexPalletReturns::class, 'inWarehouseDispatched'])->name('pallet_returns.dispatched.index');


Route::get('fulfilment-returns/{palletReturn:id}', [ShowPalletReturn::class, 'inWarehouse'])->name('pallet-returns.show')->withoutScopedBindings();
Route::get('fulfilment-return-stored-items/{palletReturn:id}', [ShowStoredItemReturn::class, 'inWarehouse'])->name('pallet-return-with-stored-items.show')->withoutScopedBindings();
Route::get('fulfilment-returns/{palletReturn:id}/pallets', IndexPalletsInReturn::class)->name('pallet-returns.pallets.index')->withoutScopedBindings();
Route::get('fulfilment-returns/{palletReturn:id}/stored-items', IndexStoredItemsInReturn::class)->name('pallet-returns.stored-items.index')->withoutScopedBindings();
