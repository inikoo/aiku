<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Catalogue\Collection\Search;

use App\Models\Catalogue\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CollectionRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Collection $collection): void
    {
        if ($collection->trashed()) {
            $collection->universalSearch()->delete();

            return;
        }

        $collection->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $collection->group_id,
                'organisation_id'   => $collection->organisation_id,
                'organisation_slug' => $collection->organisation->slug,
                'shop_id'           => $collection->shop_id,
                'shop_slug'         => $collection->shop->slug,
                'sections'          => ['catalogue'],
                'haystack_tier_1'   => trim($collection->code . ' ' . $collection->name),
                'result'            => [
                    'route'         => [
                        'name'          => 'grp.org.shops.show.catalogue.collections.show',
                            'parameters'    => [
                                $collection->organisation->slug,
                                $collection->shop->slug,
                                $collection->slug,
                            ]
                    ],
                    'code' => [
                        'label' => $collection->code,
                    ],
                    'description' => [
                        'label' => $collection->name,
                    ],
                    'icon' => [
                        'icon' => 'fal fa-album-collection',
                    ],
                ]
            ]
        );
    }

}
