<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 10:30:08 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateUniversalSearch;
use App\Actions\HydrateModel;
use App\Models\HumanResources\Employee;
use Illuminate\Support\Collection;

class UpdateEmployeeUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'employee:search {organisations?*} {--s|slugs=}';


    public function handle(Employee $employee): void
    {
        EmployeeHydrateUniversalSearch::run($employee);
    }


    protected function getModel(string $slug): Employee
    {
        return Employee::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Employee::get();
    }
}
