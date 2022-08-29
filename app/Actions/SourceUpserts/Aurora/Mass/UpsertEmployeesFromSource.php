<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:34:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

/** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\HumanResources\Employee\SetEmployeePhoto;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\HumanResources\Employee;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property \App\Models\Organisations\Organisation $organisation
 * @property \App\Models\HumanResources\Employee $employee
 */
class UpsertEmployeesFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:employees {organisation_code} {scopes?*}';


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    #[NoReturn] public function handle(Organisation $organisation, array|null $scopes = null): void
    {
        $this->organisation = $organisation;


        $validScopes = ['upsertEmployee', 'updateEmployeePhoto', 'syncJobPositions'];

        $organisationSource = app(SourceOrganisationManager::class)->make($this->organisation->type);
        $organisationSource->initialisation($this->organisation);

        foreach (
            DB::connection('aurora')
                ->table('Staff Dimension')
                ->select('Staff Key')
                ->where('Staff Currently Working', 'Yes')
                ->where('Staff Type', '!=', 'Contractor')
                ->get() as $auroraData
        ) {
            $employeeData = $organisationSource->fetchEmployee($auroraData->{'Staff Key'});


            if ($scopes == null) {
                $scopes = $validScopes;
            }

            foreach ($scopes as $scope) {
                if (!method_exists($this, $scope)) {
                    throw new Exception("Scope $scope is not supported");
                }
                $this->{$scope}($employeeData);
            }
        }
    }

    protected function upsertEmployee($employeeData): void
    {
        if ($employee = Employee::where('organisation_source_id', $employeeData['employee']['organisation_source_id'])
            ->where('organisation_id',$this->organisation->id)
            ->first()) {
            $res = UpdateEmployee::run($employee, $employeeData['employee']);
        } else {
            $res = StoreEmployee::run($this->organisation, $employeeData['employee']);
        }
        $this->employee = $res->model;
    }

    protected function syncJobPositions($employeeData): void
    {
        $this->employee->jobPositions()->sync($employeeData['job-positions']);
    }

    protected function updateEmployeePhoto($employeeData): void
    {
        foreach ($employeeData['photo'] ?? [] as $profileImage) {
            SetEmployeePhoto::run($this->employee, $profileImage['image_path'], $profileImage['filename']);
        }
    }

}
