<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 00:16:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\StoredItemAudit\CompleteStoredItemAudit;
use App\Actions\Fulfilment\StoredItemAudit\UpdateStoredItemAudit;
use Illuminate\Support\Facades\Route;

Route::patch('stored-item-audit/{storedItemAudit:id}', UpdateStoredItemAudit::class)->name('stored_item_audit.update');
Route::patch('stored-item-audit/{storedItemAudit:id}/complete', CompleteStoredItemAudit::class)->name('stored_item_audit.complete');
