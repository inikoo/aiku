<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 19:55:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;

use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\Inventory\WarehouseStats;
use App\Models\Organisations\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateOrganisation extends HydrateModel
{

    use WithNormalise;

    public string $commandSignature = 'hydrate:organisation {organisation_code?}';


    public function handle(Organisation $organisation): void
    {
        $this->employeesStats($organisation);
        $this->jobPositions($organisation);
        $this->userStats($organisation);
        $this->warehouseStats($organisation);

    }

    public function warehouseStats(Organisation $organisation)
    {
        $stats = [
            'number_warehouses' => Warehouse::where('organisation_id',$organisation->id)->count(),
            'number_warehouse_areas' => WarehouseArea::where('organisation_id',$organisation->id)->count(),
            'number_locations'=>WarehouseStats::where('organisation_id',$organisation->id)->sum('number_locations'),
            'number_locations_state_operational'=>WarehouseStats::where('organisation_id',$organisation->id)->sum('number_locations_state_operational'),
            'number_locations_state_broken'=>WarehouseStats::where('organisation_id',$organisation->id)->sum('number_locations_state_broken'),
        ];

        $organisation->inventoryStats->update($stats);
    }

    public function employeesStats($organisation)
    {
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
        $organisation->stats->update($stats);
    }

    public function userStats($organisation)
    {
        $numberUsers       = DB::table('organisation_user')->where('organisation_id', $organisation->id)
            ->count();
        $numberActiveUsers = DB::table('organisation_user')->where('organisation_id', $organisation->id)
            ->where('status', true)->count();


        $numberGuests       = DB::table('organisation_user')->where('organisation_id', $organisation->id)
            ->where('userable_type', 'Guest')
            ->count();
        $numberActiveGuests = DB::table('organisation_user')->where('organisation_id', $organisation->id)
            ->where('userable_type', 'Guest')
            ->where('status', true)
            ->count();


        $stats = [

            'number_guests'                 => $numberGuests,
            'number_guests_status_active'   => $numberActiveGuests,
            'number_guests_status_inactive' => $numberGuests - $numberActiveGuests,
            'number_users'                  => $numberUsers,
            'number_users_status_active'    => $numberActiveUsers,
            'number_users_status_inactive'  => $numberUsers - $numberActiveUsers

        ];


        foreach (
            ['organisation', 'employee', 'guest', 'supplier', 'agent', 'customer']
            as $userType
        ) {
            $stats['number_users_type_'.$userType] = 0;
        }

        foreach (
            DB::table('organisation_user')
                ->selectRaw('LOWER(userable_type) as userable_type, count(*) as total')
                ->where('organisation_id', $organisation->id)
                ->where('status', true)
                ->groupBy('userable_type')
                ->get() as $row
        ) {
            $stats['number_users_type_'.$row->userable_type] = Arr::get($row->total, $row->userable_type, 0);
        }


        $organisation->stats->update($stats);
    }

    public function jobPositions($organisation)
    {
        foreach ($organisation->jobPositions as $jobPosition) {
            $this->updateJobPositionsStats($organisation, $jobPosition);
        }
        $this->updateShareWorkTime($organisation);
    }

    public function updateJobPositionsStats(Organisation $organisation, JobPosition $jobPosition)
    {
        $organisation->jobPositions()->updateExistingPivot(
            $jobPosition->id,
            [
                'number_employees' => DB::table('employee_job_position')->where('job_position_id', $jobPosition->id)->count(),
                'number_work_time' => DB::table('employee_job_position')->where('job_position_id', $jobPosition->id)->sum('share'),
            ]
        );
    }

    public function updateShareWorkTime(Organisation $organisation)
    {
        $share = [];
        foreach ($organisation->jobPositions as $jobPosition) {
            $share[$jobPosition->id] = $jobPosition->pivot->number_work_time;
        }
        foreach ($this->normalise(collect($share)) as $id => $share) {
            $organisation->jobPositions()->updateExistingPivot(
                $id,
                [
                    'share_work_time' => $share
                ]
            );
        }
    }


    protected function getAllModels(): Collection
    {
        return Organisation::get();
    }

    public function asCommand(Command $command): void
    {
        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');

            return;
        }

        if ($command->argument('organisation_code')) {
            $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
            if ($organisation) {
                $this->handle($organisation);
                $command->info('Done!');
            }
        } else {
            $this->loopAll($command);
        }
    }

}


