<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 22:13:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Fulfilment\Fulfilment\UI\CreateFulfilment;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilments;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentOrder\UI\IndexFulfilmentOrders;
use App\Actions\Fulfilment\FulfilmentOrder\UI\ShowfulfilmentOrder;
use App\Actions\Fulfilment\Pallet\UI\CreatePallet;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\PalletDelivery\UI\ShowDeliveryPallet;
use App\Actions\Fulfilment\StoredItem\SetDamagedStoredItem;
use App\Actions\Fulfilment\StoredItem\SetReturnStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\CreateStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\EditStoredItem;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\StoredItem\UI\ShowStoredItem;
use App\Actions\Web\Webpage\IndexWebpages;
use App\Actions\Web\Website\UI\EditWebsite;
use App\Actions\Web\Website\UI\IndexWebsites;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Actions\Web\Website\UI\ShowWebsiteWorkshop;
use Illuminate\Support\Facades\Route;

//Route::prefix('shops')->name('shops.')
//    ->group(function () {
Route::get('', IndexFulfilments::class)->name('index');
Route::get('create', CreateFulfilment::class)->name('create');


Route::prefix('{fulfilment}')->name('show')
    ->group(function () {
        Route::get('', ShowFulfilment::class);

        Route::prefix("customers")
            ->name(".customers.")
            ->group(__DIR__."/customers.php");

        Route::get('/pallets', IndexPallets::class)->name('.pallets.index');
        Route::get('/pallets/create', CreatePallet::class)->name('.pallets.create');

        Route::get('delivery/{palletDelivery}/pallets/create', ShowDeliveryPallet::class)->name('.pallets.delivery.show');

        Route::prefix('websites')->name('.websites.')
            ->group(function () {
                Route::get('/', [IndexWebsites::class,'inFulfilment'])->name('index');
                Route::get('/{website}/show', ShowWebsite::class)->name('show');
                Route::get('/{website}/edit', EditWebsite::class)->name('edit');
                Route::get('/{website}/workshop', ShowWebsiteWorkshop::class)->name('workshop');

                //Route::get('/{website}/workshop/preview', ShowWebsiteWorkshopPreview::class)->name('preview');
                //Route::get('/{website}/blog/article/create', CreateArticle::class)->name('show.blog.article.create');

                // Route::get('/{website}/webpages/create', CreateWebpage::class)->name('show.webpages.create');
                Route::get('/{website}/webpages', [IndexWebpages::class, 'inWebsite'])->name('show.webpages.index');


                //Route::get('/{website}/webpages/{webpage}/create', [CreateWebpage::class, 'inWebsiteInWebpage'])->name('show.webpages.show.webpages.create');
                Route::get('/{website}/webpages/{webpage}/webpages', [IndexWebpages::class, 'inWebpage'])->name('show.webpages.show.webpages.index');
                //Route::get('/{website}/webpages/{webpage}/edit', [EditWebpage::class, 'inWebsite'])->name('show.webpages.edit');
                //Route::get('/{website}/webpages/{webpage}/workshop', [ShowWebpageWorkshop::class, 'inWebsite'])->name('show.webpages.workshop');
                //Route::post('/{website}/webpages/{webpage}/workshop/images', [UploadImagesToWebpage::class, 'inWebsite'])->name('show.webpages.workshop.images.store');


                //Route::get('/{website}/webpages/{webpage}/workshop/preview', [ShowWebpageWorkshopPreview::class, 'inWebsite'])->name('show.webpages.preview');
                //Route::get('/{website}/webpages/{webpage}', [ShowWebpage::class, 'inWebsite'])->name('show.webpages.show');

            });

    });



//   });






/*


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
