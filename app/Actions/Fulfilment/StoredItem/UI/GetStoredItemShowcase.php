<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\Concerns\AsObject;

class GetStoredItemShowcase
{
    use AsObject;

    public function handle(StoredItem $storedItem): array
    {
        return [
            'stored_item'         => StoredItemResource::make($storedItem)->getArray(),
            'pieData'             => $this->getDashboardData($storedItem),
        ];
    }

    public function getDashboardData(StoredItem $parent): array
    {
        $stats = [];

        $stats['pallets'] = [
            'label' => __('Pallet'),
            'count' => $parent->pallets()->count()
        ];

        $stats['pallets']['data'] = $parent->pallets->map(function ($pallet) {
            return [
                'label' => $pallet->name,
                'value' => $pallet->pivot->quantity
            ];
        });

        return $stats;
    }
}
