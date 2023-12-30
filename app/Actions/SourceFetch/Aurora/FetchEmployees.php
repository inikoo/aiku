<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployeeWorkingHours;
use App\Actions\Utils\StoreImage;
use App\Models\HumanResources\Employee;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchEmployees extends FetchAction
{
    public string $commandSignature = 'fetch:employees {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Employee
    {
        if ($employeeData = $organisationSource->fetchEmployee($organisationSourceId)) {
            if ($employee = Employee::where('source_id', $employeeData['employee']['source_id'])->first()) {
                $employee = UpdateEmployee::run(
                    employee: $employee,
                    modelData: $employeeData['employee']
                );
            } else {




                $workplace = $organisationSource->getOrganisation()->workplaces()->first();

                $employee = StoreEmployee::run(
                    parent: $workplace,
                    modelData: $employeeData['employee'],
                );
            }

            UpdateEmployeeWorkingHours::run($employee, $employeeData['working_hours']);


            $employee->jobPositions()->sync($employeeData['job-positions']);

            foreach ($employeeData['photo'] ?? [] as $profileImage) {
                if (isset($profileImage['image_path']) and isset($profileImage['filename'])) {
                    StoreImage::run($employee, $profileImage['image_path'], $profileImage['filename']);
                }
            }


            return $employee;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Staff Dimension')
            ->select('Staff Key as source_id')
            ->where('Staff Type', '!=', 'Contractor')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Dimension')
            ->where('Staff Type', '!=', 'Contractor')
            ->count();
    }
}
