<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 May 2024 10:42:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\Hydrators;

use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateJobPositionsShare;
use App\Actions\Traits\WithEnumStats;
use App\Actions\Traits\WithNormalise;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class JobPositionHydrateEmployees
{
    use AsAction;
    use WithNormalise;
    use WithEnumStats;

    private JobPosition $jobPosition;

    public function __construct(JobPosition $jobPosition)
    {
        $this->jobPosition = $jobPosition;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->jobPosition->id))->dontRelease()];
    }

    public function handle(JobPosition $jobPosition): void
    {


        $numberEmployees=DB::table('job_positionables')->leftJoin('employees', 'job_positionables.job_positionable_id', '=', 'employees.id')
            ->where('job_positionable_type', 'Employee')->where('job_position_id', $jobPosition->id)->count();

        $numberEmployeesWorkTime=DB::table('job_positionables')->leftJoin('employees', 'job_positionables.job_positionable_id', '=', 'employees.id')
            ->where('job_positionable_type', 'Employee')->where('job_position_id', $jobPosition->id)->sum('share');


        $stats= [
            'number_employees'           => $numberEmployees,
            'number_employees_work_time' => $numberEmployeesWorkTime
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'employees',
                field: 'state',
                enum: EmployeeStateEnum::class,
                models: Employee::class,
                where: function ($q) use ($jobPosition) {
                    $q->leftJoin('job_positionables', 'job_positionables.job_positionable_id', '=', 'employees.id')
                        ->where('job_positionable_type', 'Employee')->where('job_position_id', $jobPosition->id);
                }
            )
        );

        $stats['number_employees_currently_working']=$stats['number_employees_state_working']+ $stats['number_employees_state_leaving'];

        $jobPosition->stats()->update($stats);

        print_r($stats);

        OrganisationHydrateJobPositionsShare::run($jobPosition->organisation);
    }




}
