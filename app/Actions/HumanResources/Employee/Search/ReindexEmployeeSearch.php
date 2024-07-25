<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 12:32:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Search;

use App\Actions\HydrateModel;
use App\Models\HumanResources\Employee;
use Illuminate\Support\Collection;

class ReindexEmployeeSearch extends HydrateModel
{
    public string $commandSignature = 'employee:search {organisations?*} {--s|slugs=}';


    public function handle(Employee $employee): void
    {
        EmployeeRecordSearch::run($employee);
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
