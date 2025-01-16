<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 12:21:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Pallet\DownloadPalletsTemplate;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\Fulfilment\PalletReturn\ExportPalletReturnPallet;
use App\Actions\Fulfilment\PalletReturn\ExportPalletReturnStoredItem;
use App\Actions\Helpers\Upload\HistoryUploads;
use App\Actions\Retina\Storage\Pallet\UI\EditRetinaPallet;
use App\Actions\Retina\Storage\Pallet\UI\IndexRetinaPallets;
use App\Actions\Retina\Storage\Pallet\UI\ShowRetinaPallet;
use App\Actions\Retina\Storage\PalletDelivery\UI\IndexPalletDeliveries;
use App\Actions\Retina\Storage\PalletDelivery\UI\ShowPalletDelivery;
use App\Actions\Retina\Storage\PalletReturn\UI\IndexPalletReturns;
use App\Actions\Retina\Storage\PalletReturn\UI\ShowPalletReturn;
use App\Actions\Retina\Storage\StoredItems\UI\IndexStoredItems;
use App\Actions\Retina\Storage\StoredItemsAudit\UI\IndexRetinaStoredItemsAudits;
use App\Actions\Retina\Storage\StoredItemsAudit\UI\ShowRetinaStoredItemAudit;
use App\Actions\UI\Retina\Asset\UI\IndexRetinaAssets;
use App\Actions\UI\Retina\Storage\UI\ShowRetinaStorageDashboard;

Route::redirect('/', 'dashboard');


Route::get('/dashboard', ShowRetinaStorageDashboard::class)->name('dashboard');

Route::prefix('pallet-deliveries')->as('pallet-deliveries.')->group(function () {
    Route::get('', IndexPalletDeliveries::class)->name('index');
    Route::get('{palletDelivery}', ShowPalletDelivery::class)->name('show');
    Route::get('{palletDelivery}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');
    Route::get('{palletDelivery}/pallets-templates', DownloadPalletsTemplate::class)->name('pallets.uploads.templates');
    Route::get('{palletDelivery}/pallets-histories', [HistoryUploads::class, 'inPalletRetina'])->name('pallets.uploads.history');
});

Route::prefix('pallet-returns')->as('pallet-returns.')->group(function () {
    Route::get('', IndexPalletReturns::class)->name('index');
    Route::get('{palletReturn}', ShowPalletReturn::class)->name('show');
    Route::get('{palletReturn}/pallets/{pallet}', [ShowPallet::class, 'inFulfilmentCustomer'])->name('pallets.show');
    Route::get('{fulfilmentCustomer}/stored-items-templates', [ExportPalletReturnStoredItem::class, 'fromRetina'])->name('stored-items.uploads.templates');
    Route::get('{fulfilmentCustomer}/pallets-templates', [ExportPalletReturnPallet::class, 'fromRetina'])->name('pallets.uploads.templates');
    Route::get('{palletReturn}/upload-histories', [HistoryUploads::class, 'inPalletReturnRetina'])->name('uploads.history');
});

Route::get('pallets', IndexRetinaPallets::class)->name('pallets.index');
Route::get('pallets/{pallet}', ShowRetinaPallet::class)->name('pallets.show');
Route::get('pallets/{pallet}/edit', EditRetinaPallet::class)->name('pallets.edit');
Route::get('stored-items', IndexStoredItems::class)->name('stored-items.index');
Route::get('stored-items-audits', IndexRetinaStoredItemsAudits::class)->name('stored-items-audits.index');
Route::get('stored-items-audits/{storedItemAudit}', ShowRetinaStoredItemAudit::class)->name('stored-items-audits.show');

Route::get('assets', IndexRetinaAssets::class)->name('assets.index');

