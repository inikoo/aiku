<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Collection\UI;

use App\Models\Catalogue\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCollectionShowcase
{
    use AsObject;

    public function handle(Collection $collection): array
    {
        // dd($collection);
        return [
            'description' => $collection->description,
            'stats'       => [
                [
                    'label' => __('Department'),
                    'icon'  => 'fal fa-folder-tree',
                    'value' => $collection->stats->number_departments,
                    'meta'  => [
                        'value' => '+4',
                        'label' => __('from last month'),
                    ]
                ],
                [
                    'label' => __('Families'),
                    'icon'  => 'fal fa-folder',
                    'value' => $collection->stats->number_families,
                    'meta'  => [
                        'value' => '+4',
                        'label' => __('from last month'),
                    ]
                ],
                [
                    'label' => __('Products'),
                    'icon'  => 'fal fa-cube',
                    'value' => $collection->stats->number_products,
                    'meta'  => [
                        'value' => '+4',
                        'label' => __('from last month'),
                    ]
                ],
                [
                    'label' => __('Collections'),
                    'icon'  => 'fal fa-cube',
                    'value' => $collection->stats->number_collections,
                    'meta'  => [
                        'value' => '+4',
                        'label' => __('from last month'),
                    ]
                ],
            ],
        ];
    }
}
