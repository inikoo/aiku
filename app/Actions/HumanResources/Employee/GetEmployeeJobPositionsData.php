<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Jan 2025 13:00:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class GetEmployeeJobPositionsData
{
    use AsAction;

    public function handle(Employee  $employee): array
    {


        return (array) $employee->jobPositions()->get()->map(function ($jobPosition) {
            return [$jobPosition->slug];
        })->reduce(function ($carry, $item) {
            return array_merge_recursive($carry, $item);
        }, []);
    }
}
