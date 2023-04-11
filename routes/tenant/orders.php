<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:28:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Payment\UI\EditPayment;
use App\Actions\Accounting\Payment\UI\ShowPayment;
use App\Actions\Dispatch\DeliveryNote\ShowDeliveryNote;
use App\Actions\Sales\Order\IndexOrders;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\Sales\Order\UI\CreateOrder;
use Illuminate\Support\Facades\Route;

Route::get('/create', CreateOrder::class)->name('create');

Route::get('/', [IndexOrders::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('index');
Route::get('/{order}', [ShowOrder::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('show');
Route::get('/{order}/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, $parent == 'tenant' ? 'inTenant' : 'inOrderInShop'])->name('show.delivery-notes.show');
Route::get('/{order}/payments/{payment}', [ShowPayment::class,$parent == 'tenant' ? 'inTenant' : 'inOrderInShop'])->name('show.orders.show.payments.show');
Route::get('/{order}/payments/{payment}/edit', [EditPayment::class, $parent == 'tenant' ? 'inTenant' : 'inOrderInShop'])->name('show.orders.show.payments.edit');
