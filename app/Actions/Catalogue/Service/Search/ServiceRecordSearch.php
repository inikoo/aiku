<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:25:01 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service\Search;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Service;
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
