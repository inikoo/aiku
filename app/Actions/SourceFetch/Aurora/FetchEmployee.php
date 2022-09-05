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
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchEmployee extends FetchModel
{

    public string $commandSignature = 'fetch:employees {organisation_code} {organisation_source_id?}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Employee
    {
        if ($employeeData = $organisationSource->fetchEmployee($organisationSourceId)) {
            if ($employee = Employee::where('organisation_source_id', $employeeData['employee']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateEmployee::run(
                    employee:  $employee,
                    modelData: $employeeData['employee']
                );
            } else {
                $res = StoreEmployee::run(
                    organisation: $organisationSource->organisation,
                    modelData:    $employeeData['employee'],
                );
            }
            $employee = $res->model;
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
            ->where('Staff Type', '!=', 'Contractor')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Staff Dimension')
            ->where('Staff Currently Working', 'Yes')
            ->where('Staff Type', '!=', 'Contractor')
            ->count();
    }

}
