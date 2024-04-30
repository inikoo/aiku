<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\HumanResources\Employee;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateClockings
{
    use AsAction;
    use WithEnumStats;

    private Employee $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->employee->id))->dontRelease()];
    }

    public function handle(Employee $employee): void
    {
        $stats = [
            'number_clockings' => $employee->clockings()->count(),
        ];



        $employee->stats()->update($stats);
    }


}
