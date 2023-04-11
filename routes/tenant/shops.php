<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Accounting\Invoice\ShowInvoice;
use App\Actions\Accounting\Payment\UI\EditPayment;
use App\Actions\Accounting\Payment\UI\ShowPayment;
use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\Dispatch\DeliveryNote\ShowDeliveryNote;
use App\Actions\Leads\Prospect\IndexProspects;
use App\Actions\Mail\Outbox\IndexOutboxes;
use App\Actions\Mail\Outbox\ShowOutbox;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Order\IndexOrders;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\Sales\Order\UI\CreateOrder;
use App\Actions\Web\Website\IndexWebsites;
use App\Actions\Web\Website\ShowWebsite;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexShops::class)->name('index');
Route::get('/{shop}', ShowShop::class)->name('show');

Route::get('/{shop}/prospects', [IndexProspects::class, 'inShop'])->name('show.prospects.index');

Route::get('/{shop}/orders', [IndexOrders::class, 'inShop'])->name('show.orders.index');
Route::get('/{shop}/orders/create', [CreateOrder::class, 'inShop'])->name('show.orders.create');

Route::get('/{shop}/orders/{order}', [ShowOrder::class, 'inShop'])->name('show.orders.show');
Route::get('/{shop}/orders/{order}/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, 'inOrderInShop'])->name('show.orders.show.delivery-notes.show');
Route::get('/{shop}/orders/{order}/payments/{payment}', [ShowPayment::class, 'inOrderInShop'])->name('show.orders.show.payments.show');
Route::get('/{shop}/orders/{order}/payments/{payment}/edit', [EditPayment::class, 'inOrderInShop'])->name('show.orders.show.payments.edit');


Route::get('/{shop}/invoices', [IndexInvoices::class, 'inShop'])->name('show.invoices.index');

Route::get('/{shop}/invoices/{invoice}', [ShowInvoice::class, 'inShop'])->name('show.invoices.show');

Route::get('/{shop}/delivery-notes', [IndexDeliveryNotes::class, 'inShop'])->name('show.delivery-notes.index');

Route::get('/{shop}/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, 'inShop'])->name('show.delivery-notes.show');

Route::get('/{shop}/websites', [IndexWebsites::class, 'inShop'])->name('show.websites.index');
Route::get('/{shop}/websites/{website}', [ShowWebsite::class, 'inShop'])->name('show.websites.show');

Route::get('/{shop}/outboxes', [IndexOutboxes::class, 'inShop'])->name('show.outboxes.index');
Route::get('/{shop}/outboxes/marketing', [IndexOutboxes::class, 'inShop'])->name('show.outboxes.marketing.index');
Route::get('/{shop}/outboxes/user-notification', [IndexOutboxes::class, 'inShop'])->name('show.outboxes.user-notification.index');
Route::get('/{shop}/outboxes/customer-notification', [IndexOutboxes::class, 'inShop'])->name('show.outboxes.customer-notification.index');

Route::get('/{shop}/outboxes/{outbox}', [ShowOutbox::class, 'inShop'])->name('show.outboxes.show');
Route::get('/{shop}/outboxes/{outbox}/marketing', [ShowOutbox::class, 'inShop'])->name('show.outboxes.marketing.show');
Route::get('/{shop}/outboxes/{outbox}/user-notification', [ShowOutbox::class, 'inShop'])->name('show.outboxes.user-notification.show');
Route::get('/{shop}/outboxes/{outbox}/customer-notification', [ShowOutbox::class, 'inShop'])->name('show.outboxes.customer-notification.show');


Route::prefix("{shop}/catalogue")
    ->name("show.catalogue.")
    ->group(
        function () {
            $parent='shop';
            require __DIR__.'/catalogue.php';
        }
    );

Route::prefix("{shop}/customers")
    ->name("show.customers.")
    ->group(
        function () {
            $parent='shop';
            require __DIR__.'/customers.php';
        }
    );
