<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 23:41:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\Search;

use App\Actions\HydrateModel;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Support\Collection;

class ReindexDeliveryNotesSearch extends HydrateModel
{
    public string $commandSignature = 'delivery-note:search {organisations?*} {--s|slugs=}';


    public function handle(DeliveryNote $deliveryNote): void
    {
        DeliveryNoteRecordSearch::run($deliveryNote);
    }

    protected function getModel(string $slug): DeliveryNote
    {
        return DeliveryNote::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return DeliveryNote::withTrashed()->get();
    }
}
