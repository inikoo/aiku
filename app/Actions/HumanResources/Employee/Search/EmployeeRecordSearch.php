<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 12:32:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Search;

use App\Http\Resources\HumanResources\EmployeeSearchResultResource;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Employee $employee): void
    {
        if ($employee->trashed()) {

            if($employee->universalSearch) {
                $employee->universalSearch()->delete();
            }
            return;
        }

        $employee->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $employee->group_id,
                'organisation_id'   => $employee->organisation_id,
                'organisation_slug' => $employee->organisation->slug,
                'sections'          => ['hr'],
                'haystack_tier_1'   => trim($employee->slug.' '.$employee->worker_number.' '.$employee->contact_name),
                'haystack_tier_2'   => $employee->work_email.' '.$employee->job_title,
                'result'            => [
                    'title'      => $employee->contact_name,
                    'afterTitle' => [
                        'label' => '('.$employee->worker_number.')',
                    ],
                    'icon'       => [
                        'icon' => 'fal fa-user-hard-hat'
                    ],
                    'meta'       => EmployeeSearchResultResource::make($employee)
                ]

            ]
        );
    }


}
