<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateAccounting;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateCustomers;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateEmployees;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateInventory;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateUsers;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateWarehouse;
use App\Actions\HydrateModel;
use App\Actions\Traits\WithNormalise;
use App\Enums\Marketing\Shop\ShopStateEnum;
use App\Enums\Marketing\Shop\ShopSubtypeEnum;
use App\Enums\Marketing\Shop\ShopTypeEnum;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\SysAdmin\Guest;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HydrateTenant extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:tenant {tenants?*}';


    public function handle(): void
    {
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');

        TenantHydrateEmployees::run($tenant);
        $this->guestsStats();
        TenantHydrateWarehouse::run($tenant);
        TenantHydrateInventory::run($tenant);
        $this->procurementStats();
        $this->marketingStats();
        $this->fulfilmentStats();
        TenantHydrateUsers::run($tenant);
        TenantHydrateAccounting::run($tenant);
        TenantHydrateCustomers::run($tenant);
    }

    public function fulfilmentStats()
    {
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');
    }


    public function marketingStats()
    {
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');

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

        $tenant->marketingStats->update($stats);
    }

    public function guestsStats()
    {
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');


        $numberGuests = Guest::count();

        $numberActiveGuests = Guest::where('status', true)
            ->count();


        $stats = [
            'number_guests'                 => $numberGuests,
            'number_guests_status_active'   => $numberActiveGuests,
            'number_guests_status_inactive' => $numberGuests - $numberActiveGuests,
        ];


        $tenant->stats->update($stats);
    }


    public function procurementStats()
    {
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');
    }

    protected function getAllModels(): Collection
    {
        return Tenant::all();
    }

    public function asCommand(Command $command): int
    {
        $tenants = $this->getTenants($command);

        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(function () {
                $this->handle();
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }
}
