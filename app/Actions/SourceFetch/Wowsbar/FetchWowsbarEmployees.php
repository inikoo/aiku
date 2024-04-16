<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 Apr 2024 15:09:47 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Wowsbar;

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchWowsbarEmployees extends FetchWowsbarAction
{
    public string $commandSignature = 'fetch:wow-employees {organisations?*} {--s|source_id=} ';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Employee
    {
        if ($employeeData = $organisationSource->fetchEmployee($organisationSourceId)) {

            if ($employee = Employee::where('source_id', $employeeData['employee']['source_id'])->first()) {
                $employee = UpdateEmployee::make()->action(
                    employee: $employee,
                    modelData: $employeeData['employee']
                );
            } else {
                /* @var $workplace Workplace */
                $workplace = $organisationSource->getOrganisation()->workplaces()->first();


                $employee = StoreEmployee::make()->action(
                    parent: $workplace,
                    modelData: $employeeData['employee'],
                );
            }


            return $employee;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('wowsbar')
            ->table('employees')
            ->select('id as source_id')
            ->orderBy('source_id');

    }

    public function count(): ?int
    {
        return DB::connection('wowsbar')->table('employees')
            ->count();
    }
}
