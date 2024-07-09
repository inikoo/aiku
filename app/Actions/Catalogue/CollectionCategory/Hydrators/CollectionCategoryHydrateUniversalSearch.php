<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\CollectionCategory\Hydrators;

use App\Models\Catalogue\CollectionCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class CollectionCategoryHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(CollectionCategory $collectionCategory): void
    {
        $collectionCategory->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $collectionCategory->group_id,
                'organisation_id'   => $collectionCategory->organisation_id,
                'organisation_slug' => $collectionCategory->organisation->slug,
                'shop_id'           => $collectionCategory->shop_id,
                'shop_slug'         => $collectionCategory->shop->slug,
                'section'           => 'shops',
                'title'             => $collectionCategory->name,
                'description'       => $collectionCategory->code
            ]
        );
    }

}
