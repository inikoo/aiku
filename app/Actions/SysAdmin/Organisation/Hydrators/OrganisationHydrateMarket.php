<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Market\Shop\ShopStateEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Market\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateMarket
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_shops' => Shop::count()
        ];


        $shopStatesCount = Shop::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        foreach (ShopStateEnum::cases() as $shopState) {
            $stats['number_shops_state_'.$shopState->snake()] = Arr::get($shopStatesCount, $shopState->value, 0);
        }


        $shopTypesCount = Shop::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')->all();


        foreach (ShopTypeEnum::cases() as $shopType) {
            $stats['number_shops_type_'.$shopType->snake()] = Arr::get($shopTypesCount, $shopType->value, 0);
        }


        $organisation->marketStats->update($stats);
    }
}
