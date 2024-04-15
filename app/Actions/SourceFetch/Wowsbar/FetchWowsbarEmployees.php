<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 Apr 2024 15:09:47 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Wowsbar;

use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployeeWorkingHours;
use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\Utils\StoreImage;
use App\Enums\SysAdmin\User\UserAuthTypeEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Services\Organisation\SourceOrganisationService;
use Arr;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            /*
            UpdateEmployeeWorkingHours::run($employee, $employeeData['working_hours']);
            $employee->jobPositions()->sync($employeeData['job-positions']);

            foreach ($employeeData['photo'] ?? [] as $profileImage) {
                if (isset($profileImage['image_path']) and isset($profileImage['filename'])) {
                    StoreImage::run($employee, $profileImage['image_path'], $profileImage['filename']);
                }
            }


            if (Arr::has($employeeData, 'user')) {

                if ($employee->user) {
                    UpdateUser::make()->action(
                        $employee->user,
                        [
                            'legacy_password' => (string) Arr::get($employeeData, 'user.password'),
                            'status'          => Arr::get($employeeData, 'user.status'),
                        ]
                    );
                } else {
                    StoreUser::make()->action(
                        $employee,
                        array_merge(
                            $employeeData['user'],
                            [
                                'password'       => wordwrap(Str::random(), 4, '-', true),
                                'contact_name'   => $employee->contact_name,
                                'email'          => $employee->work_email,
                                'reset_password' => true,
                                'auth_type'      => UserAuthTypeEnum::AURORA,
                            ]
                        )
                    );
                }
            }
*/

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
