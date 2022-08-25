<?php
/** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 20:54:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\HumanResources\Employee;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Console\Command;
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
        if ($employee = Employee::where('organisation_source_id', $employeeData['employee']['organisation_source_id'])->first()) {
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


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function asCommand(Command $command): void
    {
        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');

            return;
        }


        $this->handle($organisation);
    }


}
