<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:31:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Actions\Marketing\Department\Hydrators\DepartmentHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Models\Marketing\Department;
use Lorisleiva\Actions\ActionRequest;

class UpdateDepartment
{
    use WithActionUpdate;

    public function handle(Department $department, array $modelData): Department
    {
        $department = $this->update($department, $modelData, ['data']);
        DepartmentHydrateUniversalSearch::dispatch($department);
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required'],
            'name' => ['sometimes', 'required'],
        ];
    }


    public function asController(Department $department, ActionRequest $request): Department
    {
        $request->validate();
        return $this->handle($department, $request->all());
    }


    public function jsonResponse(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }
}
