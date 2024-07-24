<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:56:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Support\Collection;

class ReindexStoredItem extends HydrateModel
{
    public string $commandSignature = 'stored-item:search {organisations?*} {--s|slugs=}';


    public function handle(StoredItem $storedItem): void
    {
        StoredItemRecordSearch::run($storedItem);
    }


    protected function getModel(string $slug): Invoice
    {
        return StoredItem::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return StoredItem::get();
    }
}
