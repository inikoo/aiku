<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 13 Nov 2024 11:58:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Search;

use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Models\Catalogue\ProductCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(ProductCategory $productCategory): void
    {
        if ($productCategory->trashed()) {
            $productCategory->universalSearch()->delete();

            return;
        }

        $productCategory->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $productCategory->group_id,
                'organisation_id'   => $productCategory->organisation_id,
                'organisation_slug' => $productCategory->organisation->slug,
                'shop_id'           => $productCategory->shop_id,
                'shop_slug'         => $productCategory->shop->slug,
                'sections'          => ['catalogue'],
                'haystack_tier_1'   => $productCategory->code,
                'haystack_tier_2'   => $productCategory->name,
                'result'            => [


                    'code' => [
                        'label' => $productCategory->code,
                    ],

                    'description' => [
                        'label' => $productCategory->name,
                    ],
                    'stats'       => match ($productCategory->type) {
                        ProductCategoryTypeEnum::DEPARTMENT, ProductCategoryTypeEnum::SUB_DEPARTMENT => [
                            [
                                'label' => __('Families'),
                                'icon'  => 'fal fa-folder',
                                'value' => $productCategory->stats->number_current_families,
                            ],
                            [
                                'label' => __('Products'),
                                'icon'  => 'fal fa-cube',
                                'value' => $productCategory->stats->number_current_products
                            ]
                        ],
                        ProductCategoryTypeEnum::FAMILY => [
                            [
                                'label' => __('Products'),
                                'icon'  => 'fal fa-cube',
                                'value' => $productCategory->stats->number_current_products
                            ]
                        ]
                    },
                    'icon'        => match ($productCategory->type) {
                            ProductCategoryTypeEnum::FAMILY => [
                                'icon' => 'fal fa-folder',
                            ],
                            default => [
                                'icon' => 'fal fa-folder-tree',
                            ],
                        },
                    'state_icon' => $productCategory->state->stateIcon()[$productCategory->state->value]


                ]
            ]
        );
    }

}
