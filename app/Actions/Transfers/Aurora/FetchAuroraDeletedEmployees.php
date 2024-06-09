<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Feb 2023 12:33:39 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Models\HumanResources\Employee;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchAuroraDeletedEmployees extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-employees {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Employee
    {
        if ($employeeData = $organisationSource->fetchDeletedEmployee($organisationSourceId)) {
            if ($employee = Employee::withTrashed()->where('source_id', $employeeData['employee']['source_id'])->first()) {
                $employee = UpdateEmployee::run(
                    employee:  $employee,
                    modelData: $employeeData['employee']
                );
            } else {
                $workplace = $organisationSource->getOrganisation()->workplaces()->first();
                $employee  = StoreEmployee::run(
                    parent: $workplace,
                    modelData:    $employeeData['employee'],
                );
            }
            return $employee;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Staff Deleted Dimension')
            ->select('Staff Deleted Key as source_id')
            ->where('Staff Deleted Type', '!=', 'Contractor')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Deleted Dimension')
            ->where('Staff Deleted Type', '!=', 'Contractor')
            ->count();
    }
}
