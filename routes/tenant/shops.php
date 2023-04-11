<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\IndexInvoices;
use App\Actions\Accounting\Invoice\ShowInvoice;
use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\Dispatch\DeliveryNote\ShowDeliveryNote;
use App\Actions\Leads\Prospect\IndexProspects;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Web\Website\IndexWebsites;
use App\Actions\Web\Website\ShowWebsite;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexShops::class)->name('index');
Route::get('/{shop}', ShowShop::class)->name('show');

Route::get('/{shop}/prospects', [IndexProspects::class, 'inShop'])->name('show.prospects.index');

Route::get('/{shop}/invoices', [IndexInvoices::class, 'inShop'])->name('show.invoices.index');

Route::get('/{shop}/invoices/{invoice}', [ShowInvoice::class, 'inShop'])->name('show.invoices.show');

Route::get('/{shop}/delivery-notes', [IndexDeliveryNotes::class, 'inShop'])->name('show.delivery-notes.index');

Route::get('/{shop}/delivery-notes/{deliveryNote}', [ShowDeliveryNote::class, 'inShop'])->name('show.delivery-notes.show');

Route::get('/{shop}/websites', [IndexWebsites::class, 'inShop'])->name('show.websites.index');
Route::get('/{shop}/websites/{website}', [ShowWebsite::class, 'inShop'])->name('show.websites.show');

Route::prefix("{shop}")
    ->name("show.")
    ->group(
        function () {
            $parent='shop';
            require __DIR__.'/invoice-payments.php';
        }
    );

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
Route::prefix("{shop}/orders")
    ->name("show.orders.")
    ->group(
        function () {
            $parent='shop';
            require __DIR__.'/orders.php';
        }
    );

Route::prefix("{shop}/mail")
    ->name("show.mail.")
    ->group(
        function () {
            $parent='shop';
            require __DIR__.'/mail.php';
        }
    );
