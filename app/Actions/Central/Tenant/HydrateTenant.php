<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\Hydrators\HydrateModel;
use App\Actions\Traits\WithNormalise;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Inventory\WarehouseStats;
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
        $this->employeesStats();
        $this->guestsStats();
        $this->userStats();
        $this->warehouseStats();
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

    public function userStats()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $numberUsers       = DB::table('users')->count();
        $numberActiveUsers = DB::table('users')->where('status', true)->count();



        $stats = [
            'number_users'                  => $numberUsers,
            'number_users_status_active'    => $numberActiveUsers,
            'number_users_status_inactive'  => $numberUsers - $numberActiveUsers

        ];


        foreach (
            ['employee', 'guest', 'supplier', 'agent', 'customer']
            as $userType
        ) {
            $stats['number_users_type_'.$userType] = 0;
        }

        foreach (
            DB::table('users')
                ->selectRaw('LOWER(parent_type) as parent_type, count(*) as total')
                ->where('status', true)
                ->groupBy('parent_type')
                ->get() as $row
        ) {
            $stats['number_users_type_'.$row->parent_type] = Arr::get($row->total, $row->parent_type, 0);
        }

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


