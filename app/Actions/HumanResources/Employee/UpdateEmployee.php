<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;


use App\Models\Utils\ActionResult;
use App\Actions\WithUpdate;
use App\Models\HumanResources\Employee;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;


class UpdateEmployee
{
    use AsAction;
    use WithUpdate;

    public function handle(Employee $employee, array $employeeData): ActionResult
    {
        $res = new ActionResult();


        $employee->update(
            Arr::except($employeeData, [
                'data',
                'salary',
                'working_hours',

            ])
        );
        $employee->update($this->extractJson($employeeData, ['data', 'salary', 'working_hours']));

        $res->changes = $employee->getChanges();



        $res->model = $employee;

        $res->model_id = $employee->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';


        return $res;
    }



}
