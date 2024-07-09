<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateUniversalSearch
{
    use AsAction;
    public string $jobQueue = 'universal-search';

    public function handle(Employee $employee): void
    {
        $employee->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'         => $employee->group_id,
                'organisation_id'  => $employee->organisation_id,
                'organisation_slug'=> $employee->organisation->slug,
                'section'          => 'hr',
                'title'            => trim($employee->slug . ' ' . $employee->worker_number . ' ' . $employee->contact_name),
                'description'      => $employee->work_email . ' ' . $employee->job_title
            ]
        );
    }


}
