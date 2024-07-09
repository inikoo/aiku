<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Models\Catalogue\ProductCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(ProductCategory $productCategory): void
    {
        $productCategory->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $productCategory->group_id,
                'organisation_id'   => $productCategory->organisation_id,
                'organisation_slug' => $productCategory->organisation->slug,
                'shop_id'           => $productCategory->shop_id,
                'shop_slug'         => $productCategory->shop->slug,
                'section'           => 'shops',
                'title'             => $productCategory->name,
                'description'       => $productCategory->code
            ]
        );
    }

}
