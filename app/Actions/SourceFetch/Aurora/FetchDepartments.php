<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:14:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\Department\StoreDepartment;
use App\Actions\Marketing\Department\UpdateDepartment;
use App\Models\Marketing\Department;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchDepartments
{
    use AsAction;


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Department
    {
        if ($departmentData = $tenantSource->fetchDepartment($tenantSourceId)) {
            if ($department = Department::where('source_id', $departmentData['department']['source_id'])
                ->first()) {
                $department = UpdateDepartment::run(
                    department: $department,
                    modelData:  $departmentData['department'],
                );
            } else {
                $department = StoreDepartment::run(
                    shop:      $departmentData['shop'],
                    modelData: $departmentData['department']
                );
            }

            return $department;
        }


        return null;
    }
}
