<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Mar 2025 21:32:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Dispatching\DeliveryNote\PdfDeliveryNote;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\Dispatching\DeliveryNote\UI\ShowDeliveryNote;
use App\Actions\UI\Dispatch\ShowAgentDispatchHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowAgentDispatchHub::class)->name('backlog');
Route::get('/stock-deliveries', IndexDeliveryNotes::class)->name('stock_deliveries');
Route::get('/stock-deliveries/unassigned', [IndexDeliveryNotes::class, 'unassigned'])->name('unassigned.stock_deliveries');
Route::get('/stock-deliveries/queued', [IndexDeliveryNotes::class, 'queued'])->name('queued.stock_deliveries');
Route::get('/stock-deliveries/handling', [IndexDeliveryNotes::class, 'handling'])->name('handling.stock_deliveries');
Route::get('/stock-deliveries/handling-blocked', [IndexDeliveryNotes::class, 'handlingBlocked'])->name('handling-blocked.stock_deliveries');
Route::get('/stock-deliveries/packed', [IndexDeliveryNotes::class, 'packed'])->name('packed.stock_deliveries');
Route::get('/stock-deliveries/finalised', [IndexDeliveryNotes::class, 'finalised'])->name('finalised.stock_deliveries');
Route::get('/stock-deliveries/dispatched', [IndexDeliveryNotes::class, 'dispatched'])->name('dispatched.stock_deliveries');
Route::get('/stock-deliveries/{deliveryNote}', [ShowDeliveryNote::class, 'inWarehouse'])->name('stock_deliveries.show');
Route::get('/stock-deliveries/{deliveryNote}/pdf', PdfDeliveryNote::class)->name('stock_deliveries.pdf');
