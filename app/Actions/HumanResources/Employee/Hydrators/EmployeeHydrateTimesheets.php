<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 19:27:30 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\HumanResources\Employee;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateTimesheets
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
            'number_timesheets' => $employee->timesheets()->count(),
        ];


        $employee->stats()->update($stats);
    }


}
