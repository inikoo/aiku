<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\Pallet;
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
            'route_pallets'       => [
                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.stored-item.index',
                'parameters' => [
                    'organisation'         => $storedItem->organisation->slug,
                    'fulfilment'           => $storedItem->fulfilment->slug,
                    'fulfilmentCustomer'   => $storedItem->fulfilmentCustomer->slug,
                    'storedItem'           => $storedItem->slug
                ]
            ]
        ];
    }

    public function getDashboardData(StoredItem $parent): array
    {
        // $stats = [];

        // $stats['pallets'] = [
        //     'label' => __('Pallet'),
        //     'count' => $parent->pallets()->count()
        // ];

        // $stats['pallets']['data'] = $parent->pallets->map(function (Pallet $pallet) {
        //     return [
        //         'label' => $pallet->reference,
        //         'value' => (int) $pallet->pivot->quantity
        //     ];
        // });

        $stats          = [];
        $stats['stats'] = $parent->pallets->map(function (Pallet $pallet) {
            return [
                'label' => $pallet->reference,
                'value' => (int) $pallet->pivot->quantity
            ];
        });

        return $stats;
    }
}
