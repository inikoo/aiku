<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Dispatch\DeliveryNote\ShowDeliveryNote;
use App\Actions\Sales\Order\IndexOrders;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\Sales\Order\UI\CreateOrder;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexOrders::class)->name('index');
Route::get('/create', CreateOrder::class)->name('create');
Route::get('/{order}', ShowOrder::class)->name('show');
Route::get('/{order}/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, 'inOrder'])->name('show.delivery-notes.show');
