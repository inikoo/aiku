<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 12:33:52 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\Dispatching\DeliveryNote\UI\ShowDeliveryNote;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInReturn;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItemsInReturn;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDispatchHub::class)->name('backlog');
Route::get('/delivery-notes', IndexDeliveryNotes::class)->name('delivery-notes');
Route::get('/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, 'inWarehouse'])->name('delivery-notes.show');

Route::get('fulfilment-returns', [IndexPalletReturns::class, 'inWarehouse'])->name('pallet-returns.index');
Route::get('fulfilment-returns/{palletReturn:id}', [ShowPalletReturn::class, 'inWarehouse'])->name('pallet-returns.show')->withoutScopedBindings();
Route::get('fulfilment-returns/{palletReturn:id}/pallets', IndexPalletsInReturn::class)->name('pallet-returns.pallets.index')->withoutScopedBindings();
Route::get('fulfilment-returns/{palletReturn:id}/stored-items', IndexStoredItemsInReturn::class)->name('pallet-returns.stored-items.index')->withoutScopedBindings();
