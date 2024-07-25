<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 18:37:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\StoredItemAudit;
use Illuminate\Support\Collection;

class ReindexStoredItemAuditSearch extends HydrateModel
{
    public string $commandSignature = 'stored-item-audit:search {organisations?*} {--s|slugs=}';


    public function handle(StoredItemAudit $storedItemAudit): void
    {
        StoredItemAuditRecordSearch::run($storedItemAudit);
    }

    protected function getModel(string $slug): Invoice
    {
        return StoredItemAudit::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return StoredItemAudit::get();
    }
}
