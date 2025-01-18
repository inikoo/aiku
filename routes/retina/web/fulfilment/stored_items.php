<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Jan 2025 02:45:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Retina\Fulfilment\StoredItems\UI\IndexRetinaStoredItems;
use App\Actions\Retina\Fulfilment\StoredItemsAudit\UI\IndexRetinaStoredItemsAudits;
use App\Actions\Retina\Fulfilment\StoredItemsAudit\UI\ShowRetinaStoredItemAudit;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app/fulfilment/itemised-storage/skus');

Route::get('skus', IndexRetinaStoredItems::class)->name('stored_items.index');
Route::get('audits', IndexRetinaStoredItemsAudits::class)->name('stored_items_audits.index');
Route::get('audits/{storedItemAudit}', ShowRetinaStoredItemAudit::class)->name('stored_items_audits.show');
