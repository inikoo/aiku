<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:22:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Service\Search;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Billables\Service;
use Lorisleiva\Actions\Concerns\AsAction;

class ServiceRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Service $service): void
    {
        if ($service->trashed()) {
            $service->universalSearch()->delete();
            return;
        }

        $shop = $service->shop;

        $modelData =
            [
                'group_id'          => $service->group_id,
                'organisation_id'   => $service->organisation_id,
                'organisation_slug' => $service->organisation->slug,
                'shop_id'           => $service->shop_id,
                'shop_slug'         => $service->shop->slug,
                'sections'          => ['catalogue'],
                'haystack_tier_1'   => $service->name,
                'result'            => [
                    'route'     => [
                        'name'          => 'grp.org.fulfilments.show.catalogue.services.show',
                        'parameters'    => [
                            $service->organisation->slug,
                            $service->shop->slug,
                            $service->slug
                        ]
                    ],
                    // 'container'     => [
                    //     'key'     => 'address',
                    //     'label'   => $service->customer->location
                    // ],
                    'title'         => $service->name,
                    'afterTitle'    => [
                        'label'     => '(' . $service->code . ')',
                    ],
                    'icon'          => [
                        'icon'  => 'fal fa-concierge-bell',
                    ],
                    'meta'          => [
                        [
                            'key'       => 'created_date',
                            'type'      => 'date',
                            'label'     => $service->created_at,
                            'tooltip'   => __('Created at')
                        ],
                        [
                            'key'       => 'price',
                            'type'      => 'currency',
                            'code'      => $service->currency->code,
                            'amount'    => $service->price,
                            'tooltip'   => __('Price')
                        ],
                    ],
                ]
            ];

        if ($shop->type == ShopTypeEnum::FULFILMENT) {
            $modelData['fulfilment_id']   = $shop->fulfilment->id;
            $modelData['fulfilment_slug'] = $shop->fulfilment->slug;
        }

        $service->universalSearch()->updateOrCreate(
            [],
            $modelData
        );
    }

}
