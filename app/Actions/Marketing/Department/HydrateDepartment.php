<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 15 Feb 2022 22:35:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Department;

use App\Actions\HydrateModel;
use App\Actions\Marketing\Department\Hydrators\DepartmentHydrateFamilies;
use App\Actions\Marketing\Department\Hydrators\DepartmentHydrateProducts;
use App\Models\Marketing\Department;
use Illuminate\Support\Collection;

class HydrateDepartment extends HydrateModel
{
    public string $commandSignature = 'hydrate:department {tenants?*} {--i|id=} ';


    public function handle(Department $department): void
    {
        DepartmentHydrateFamilies::run($department);
        DepartmentHydrateProducts::run($department);
    }


    protected function getModel(int $id): Department
    {
        return Department::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Department::withTrashed()->get();
    }
}
