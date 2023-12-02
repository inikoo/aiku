<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 15 Aug 2023 12:15:47 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Models\HumanResources\Employee;
use App\Models\Grouping\Organisation;
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
