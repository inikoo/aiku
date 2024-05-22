<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 May 2024 19:08:16 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateJobPositionsShare;
use App\Actions\HumanResources\JobPosition\Hydrators\JobPositionHydrateEmployees;
use App\Actions\SysAdmin\User\SyncRolesFromJobPositions;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\Inventory\Warehouse;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class AddJobPositionToEmployee
{
    use AsAction;

    public function handle(Employee $employee, JobPosition $jobPosition, array $scopes = []): Employee
    {
        if ($employee->jobPositions()->where('job_positions.id', $jobPosition->id)->exists()) {
            return $employee;
        }

        $employee->jobPositions()->attach(
            $jobPosition->id,
            [
                'scopes' => $scopes
            ]

        );

        if ($employee->user) {
            SyncRolesFromJobPositions::run($employee->user);
        }
        EmployeeHydrateJobPositionsShare::run($employee);
        JobPositionHydrateEmployees::run($jobPosition);
        return $employee;
    }

    public string $commandSignature = 'employee:add_job_position {employee} {job_position} {--w|warehouses=*} {--s|shops=*} {--f|fulfilments=*}';

    public function asCommand(Command $command): int
    {


        try {
            $employee = Employee::where('slug', $command->argument('employee'))->firstOrFail();
        } catch (Exception) {
            $command->error('Employee not found');

            return 1;
        }

        app()->instance('group', $employee->organisation->group);
        setPermissionsTeamId($employee->organisation->group_id);

        try {
            $jobPosition = JobPosition::where('slug', $command->argument('job_position'))->firstOrFail();
        } catch (Exception) {
            $command->error('Job position not found');

            return 1;
        }

        $scopes = [];

        if ($employee->jobPositions()->where('job_positions.id', $jobPosition->id)->exists()) {
            $command->error('Job position already added to employee');
            return 1;
        }


        if ($command->options()['fulfilments']) {
            foreach ($command->options()['fulfilments'] as $fulfilmentSlug) {
                /** @var Fulfilment $fulfilment */
                $fulfilment = $employee->organisation->fulfilments()->where('slug', $fulfilmentSlug)->first();
                if ($fulfilment) {
                    $scopes['Fulfilment'][] = $fulfilment->id;
                }else{
                    $command->error("Fulfillment $fulfilmentSlug not found");
                    return 1;
                }
            }
        }

        if ($command->options()['warehouses']) {
            foreach ($command->options()['warehouses'] as $warehouseSlug) {
                /** @var Warehouse $warehouse */
                $warehouse = $employee->organisation->warehouses()->where('slug', $warehouseSlug)->first();
                if ($warehouse) {
                    $scopes['Warehouse'][] = $warehouse->id;
                }else{
                    $command->error("Warehouse $warehouseSlug not found");
                    return 1;
                }
            }
        }

        if ($command->options()['shops']) {
            foreach ($command->options()['shops'] as $shopSlug) {
                /** @var Shop $shop */
                $shop = $employee->organisation->shops()->where('slug', $shopSlug)->first();
                if ($shop) {
                    $scopes['Shop'][] = $shop->id;
                }else{
                    $command->error("Shop $shopSlug not found");
                    return 1;
                }
            }
        }




        if ($this->handle($employee, $jobPosition, $scopes)) {
            $command->info('Job position '.$jobPosition->code.' added to employee: '.$employee->alias);

            return 0;
        } else {
            $command->error('Error adding job position'.$employee->alias);

            return 1;
        }
    }


}
