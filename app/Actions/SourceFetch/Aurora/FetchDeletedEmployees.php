<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Feb 2023 12:33:39 Malaysia Time, Ubud, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\Utils\SetPhoto;
use App\Models\HumanResources\Employee;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchDeletedEmployees extends FetchAction
{

    public string $commandSignature = 'fetch:deleted-employees {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Employee
    {
        if ($employeeData = $tenantSource->fetchDeletedEmployee($tenantSourceId)) {

            if ($employee = Employee::withTrashed()->where('source_id', $employeeData['employee']['source_id'])->first()) {
                $employee = UpdateEmployee::run(
                    employee:  $employee,
                    modelData: $employeeData['employee']
                );
            } else {
                $employee = StoreEmployee::run(
                    modelData:    $employeeData['employee'],
                );
            }
            return $employee;
        }

        return null;
    }

    function getModelsQuery(): Builder
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

    function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Deleted Dimension')
            ->where('Staff Deleted Type', '!=', 'Contractor')
            ->count();
    }

}
