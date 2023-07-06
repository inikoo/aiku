<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jul 2023 12:43:29 Malaysia Time, plane Bali - KL
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant\Hydrators;

use App\Enums\Market\Shop\ShopStateEnum;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateMarket
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
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

        $shopSubtypesCount = Shop::selectRaw('subtype, count(*) as total')
            ->groupBy('subtype')
            ->pluck('total', 'subtype')->all();


        foreach (ShopSubtypeEnum::cases() as $shopSubtype) {
            $stats['number_shops_subtype_'.$shopSubtype->snake()] = Arr::get($shopSubtypesCount, $shopSubtype->value, 0);
        }

        $shopStatesSubtypesCount = Shop::selectRaw("concat(state,'_',subtype) as state_subtype, count(*) as total")
            ->groupBy('state', 'state_subtype')
            ->pluck('total', 'state_subtype')->all();


        foreach (ShopStateEnum::cases() as $shopState) {
            foreach (ShopSubtypeEnum::cases() as $shopSubtype) {
                $stats['number_shops_state_subtype_'.$shopState->snake().'_'.$shopSubtype->snake()] = Arr::get($shopStatesSubtypesCount, $shopState->value.'_'.$shopSubtype->value, 0);
            }
        }

        $tenant->marketStats->update($stats);
    }
}
