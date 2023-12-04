<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateEmployees
{
    use AsAction;
    use WithEnumStats;

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_employees' => $organisation->employees()->count()
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'employees',
                field: 'state',
                enum: EmployeeStateEnum::class,
                models: Employee::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'employees',
                field: 'type',
                enum: EmployeeTypeEnum::class,
                models: Employee::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $organisation->humanResourcesStats()->update($stats);
    }
}
