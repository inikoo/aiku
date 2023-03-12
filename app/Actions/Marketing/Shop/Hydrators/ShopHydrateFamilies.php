<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 17:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateFamilies implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shop $shop): void
    {
        $familyStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts  = Family::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats        = [
            'number_families' => $shop->families->count(),
        ];
        foreach ($familyStates as $familyState) {
            $stats['number_families_state_'.str_replace('-', '_', $familyState)] = Arr::get($stateCounts, $familyState, 0);
        }
        $shop->stats->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }
}
