<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateAccounting;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateUsers;
use App\Actions\HydrateModel;
use App\Actions\Traits\WithNormalise;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Inventory\WarehouseStats;
use App\Models\Marketing\Shop;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Sales\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateTenant extends HydrateModel
{

    use WithNormalise;

    public string $commandSignature = 'hydrate:tenant {tenants?*}';


    public function handle(): void
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $this->employeesStats();
        $this->guestsStats();
        $this->warehouseStats();
        $this->inventoryStats();
        $this->procurementStats();
        $this->marketingStats();
        $this->fulfilmentStats();
        TenantHydrateUsers::run($tenant);
        TenantHydrateAccounting::run($tenant);
    }

    public function fulfilmentStats(){
        /** @var Tenant $tenant */
        $tenant = tenant();
    }




    public function customersStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $stats = [
            'number_customers' => Customer::count()
        ];


        $customerStates      = ['in-process', 'active', 'losing', 'lost', 'registered'];
        $customerStatesCount = Customer::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($customerStates as $customerState) {
            $stats['number_customers_state_'.str_replace('-', '_', $customerState)] = Arr::get($customerStatesCount, $customerState, 0);
        }

        $customerTradeStates      = ['none', 'one', 'many'];
        $customerTradeStatesCount = Customer::selectRaw('trade_state, count(*) as total')
            ->groupBy('trade_state')
            ->pluck('total', 'trade_state')->all();

        foreach ($customerTradeStates as $customerTradeState) {
            $stats['number_customers_trade_state_'.$customerTradeState] = Arr::get($customerTradeStatesCount, $customerTradeState, 0);
        }


        $tenant->salesStats->update($stats);
    }

    public function marketingStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $stats = [
            'number_shops' => Shop::count()
        ];


        $shopStates = ['in-process', 'open', 'closing-down', 'closed'];
        $shopStatesCount = Shop::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        foreach ($shopStates as $shopState) {
            $stats['number_shops_state_'.preg_replace('/-/','_',$shopState)] = Arr::get($shopStatesCount, preg_replace('/-/','_',$shopState), 0);
        }

        $shopTypes      = ['shop', 'fulfilment_house', 'agent'];
        $shopTypesCount = Shop::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')->all();


        foreach ($shopTypes as $shopType) {
            $stats['number_shops_type_'.$shopType] = Arr::get($shopTypesCount, $shopType, 0);
        }

        $shopSubtypes      = ['b2b', 'b2c', 'storage', 'fulfilment', 'dropshipping'];
        $shopSubtypesCount = Shop::selectRaw('subtype, count(*) as total')
            ->groupBy('subtype')
            ->pluck('total', 'subtype')->all();


        foreach ($shopSubtypes as $shopSubtype) {
            $stats['number_shops_subtype_'.$shopSubtype] = Arr::get($shopSubtypesCount, $shopSubtype, 0);
        }

        $shopStatesSubtypesCount = Shop::selectRaw("concat(state,'_',subtype) as state_subtype, count(*) as total")
            ->groupBy('state','state_subtype')
            ->pluck('total', 'state_subtype')->all();


        foreach ($shopStates as $shopState) {
            foreach ($shopSubtypes as $shopSubtype) {
                $stats['number_shops_state_subtype_'.preg_replace('/-/','_',$shopState).'_'.$shopSubtype] = Arr::get($shopStatesSubtypesCount, preg_replace('/-/','_',$shopState).'_'.$shopSubtype, 0);
            }
        }

        $tenant->marketingStats->update($stats);
    }

    public function warehouseStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();
        $stats  = [
            'number_warehouses'                  => Warehouse::count(),
            'number_warehouse_areas'             => WarehouseArea::count(),
            'number_locations'                   => WarehouseStats::sum('number_locations'),
            'number_locations_state_operational' => WarehouseStats::sum('number_locations_state_operational'),
            'number_locations_state_broken'      => WarehouseStats::sum('number_locations_state_broken'),
        ];

        $tenant->inventoryStats->update($stats);
    }

    public function employeesStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $stats = [
            'number_employees' => Employee::count()
        ];

        $employeeStates     = ['hired', 'working', 'left'];
        $employeeStateCount = Employee::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($employeeStates as $employeeState) {
            $stats['number_employees_state_'.$employeeState] = Arr::get($employeeStateCount, $employeeState, 0);
        }

        $tenant->stats->update($stats);
    }

    public function guestsStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();


        $numberGuests       = DB::table('guests')
            ->count();
        $numberActiveGuests = DB::table('guests')
            ->where('status', true)
            ->count();


        $stats = [
            'number_guests'                 => $numberGuests,
            'number_guests_status_active'   => $numberActiveGuests,
            'number_guests_status_inactive' => $numberGuests - $numberActiveGuests,
        ];


        $tenant->stats->update($stats);
    }


    public function inventoryStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();
        $stats  = [
            'number_stocks'         => Stock::count(),
            'number_stock_families' => StockFamily::count(),
        ];

        $stockFamilyStates     = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stockFamilyStateCount = StockFamily::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($stockFamilyStates as $stockFamilyState) {
            $stats['number_stock_families_state_'.str_replace('-', '_', $stockFamilyState)] = Arr::get($stockFamilyStateCount, $stockFamilyState, 0);
        }

        $stockStates     = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stockStateCount = Stock::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($stockStates as $stockState) {
            $stats['number_stocks_state_'.str_replace('-', '_', $stockState)] = Arr::get($stockStateCount, $stockState, 0);
        }

        $tenant->inventoryStats->update($stats);
    }

    public function procurementStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();


        $stats = [
            'number_suppliers'        => Supplier::where('type', 'supplier')->count(),
            'number_active_suppliers' => Supplier::where('type', 'supplier')->where('status', true)->count(),

            'number_agents'               => Agent::count(),
            'number_active_agents'        => Agent::where('status', true)->count(),
            'number_active_tenant_agents' => Agent::where('status', true)->whereNull('global_id')->count(),
            'number_active_global_agents' => Agent::where('status', true)->whereNotNull('global_id')->count(),

        ];


        $tenant->procurementStats->update($stats);
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
            $result = (int)$tenant->run(function () {
                $this->handle();
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


}


