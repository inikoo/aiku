<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\HumanResources\Employee\SetEmployeePhoto;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Models\HumanResources\Employee;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchEmployees extends FetchAction
{

    public string $commandSignature = 'fetch:employees {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Employee
    {
        if ($employeeData = $tenantSource->fetchEmployee($tenantSourceId)) {
            if ($employee = Employee::where('source_id', $employeeData['employee']['source_id'])->first()) {
                $employee = UpdateEmployee::run(
                    employee:  $employee,
                    modelData: $employeeData['employee']
                );
            } else {
                $employee = StoreEmployee::run(
                    modelData:    $employeeData['employee'],
                );
            }
            $employee->jobPositions()->sync($employeeData['job-positions']);

            foreach ($employeeData['photo'] ?? [] as $profileImage) {
                SetEmployeePhoto::run($employee, $profileImage['image_path'], $profileImage['filename']);
            }

            $this->progressBar?->advance();

            return $employee;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Staff Dimension')
            ->select('Staff Key as source_id')
            ->where('Staff Currently Working', 'Yes')
            ->where('Staff Type', '!=', 'Contractor');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Dimension')
            ->where('Staff Currently Working', 'Yes')
            ->where('Staff Type', '!=', 'Contractor')
            ->count();
    }

}
