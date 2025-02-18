<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 12:33:52 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatching\DeliveryNote\PdfDeliveryNote;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\Dispatching\DeliveryNote\UI\ShowDeliveryNote;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Fulfilment\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Fulfilment\PalletReturn\UI\ShowStoredItemReturn;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDispatchHub::class)->name('backlog');
Route::get('/delivery-notes', IndexDeliveryNotes::class)->name('delivery-notes');
Route::get('/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, 'inWarehouse'])->name('delivery-notes.show');
Route::get('/delivery-notes/{deliveryNote}/pdf', PdfDeliveryNote::class)->name('delivery-notes.pdf');

Route::get('returns', [IndexPalletReturns::class, 'inWarehouse'])->name('pallet-returns.index');
Route::get('returns/{palletReturn}', [ShowPalletReturn::class, 'inWarehouse'])->name('pallet-returns.show');
Route::get('return-stored-items/{palletReturn}', [ShowStoredItemReturn::class, 'inWarehouse'])->name('pallet-return-with-stored-items.show');
