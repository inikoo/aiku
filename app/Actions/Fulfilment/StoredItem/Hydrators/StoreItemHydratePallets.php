<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Feb 2025 23:29:35 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithEnumStats;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class StoreItemHydratePallets extends HydrateModel
{
    use WithActionUpdate;
    use WithEnumStats;

    private StoredItem $storedItem;

    public function __construct(StoredItem $storedItem)
    {
        $this->storedItem = $storedItem;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->storedItem->id))->dontRelease()];
    }

    public function handle(StoredItem $storedItem): void
    {

        $stats = [
            'number_pallets' => DB::table('pallet_stored_items')
                ->where('stored_item_id', $storedItem->id)
                ->where('in_process', false)->count(),
        ];



        $storedItem->update($stats);
    }
}
