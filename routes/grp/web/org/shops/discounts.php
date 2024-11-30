<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Discounts\Offer\UI\IndexOffers;
use App\Actions\Discounts\OfferCampaign\UI\IndexOfferCampaigns;
use App\Actions\Discounts\OfferCampaign\UI\ShowOfferCampaign;
use App\Actions\UI\Dropshipping\Offers\ShowOfferDashboard;
use App\Stubs\UIDummies\CreateDummy;
use App\Stubs\UIDummies\EditDummy;
use App\Stubs\UIDummies\ShowDummy;
use Illuminate\Support\Facades\Route;

Route::get('', ShowOfferDashboard::class)->name('dashboard');
Route::name("campaigns.")->prefix('campaigns')
    ->group(function () {
        Route::get('', IndexOfferCampaigns::class)->name('index');
        Route::get('{offerCampaign}', ShowOfferCampaign::class)->name('show');
        Route::get('{offerCampaign}/edit', EditDummy::class)->name('edit');
    });

Route::name("offers.")->prefix('offers')
    ->group(function () {
        Route::get('', IndexOffers::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');
        Route::get('{offer}', ShowDummy::class)->name('show');
        Route::get('{offer}/edit', EditDummy::class)->name('edit');
    });
