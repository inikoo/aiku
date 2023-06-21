<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\HydrateModel;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateAccounting;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateCustomers;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateEmployees;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateOrders;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateUsers;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateWarehouse;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateWeb;
use App\Actions\Traits\WithNormalise;
use App\Enums\Market\Shop\ShopStateEnum;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Auth\Guest;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HydrateTenant extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:tenants {tenants?*}';


    public function handle(): void
    {
        /** @var \App\Models\Tenancy\Tenant $tenant */
        $tenant = app('currentTenant');
        TenantHydrateEmployees::run($tenant);
        $this->guestsStats();
        TenantHydrateWarehouse::run($tenant);
        TenantHydrateInventory::run($tenant);
        $this->marketingStats();
        $this->fulfilmentStats();
        TenantHydrateUsers::run($tenant);
        TenantHydrateAccounting::run($tenant);
        TenantHydrateCustomers::run($tenant);
        TenantHydrateOrders::run($tenant);
        TenantHydrateProcurement::run($tenant);
        TenantHydrateWeb::run($tenant);
    }

    public function fulfilmentStats()
    {
        /** @var \App\Models\Tenancy\Tenant $tenant */
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

    public function guestsStats(): void
    {
        /** @var \App\Models\Tenancy\Tenant $tenant */
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
