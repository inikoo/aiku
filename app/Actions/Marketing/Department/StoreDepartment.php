<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Actions\Marketing\Department\Hydrators\DepartmentHydrateUniversalSearch;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateDepartments;
use App\Enums\Marketing\Department\DepartmentTypeEnum;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreDepartment
{
    use AsAction;
    use WithAttributes;

    public function handle(Shop|Department $parent, array $modelData): Department
    {
        if (class_basename($parent) == 'Department') {
            $modelData['type'] = DepartmentTypeEnum::BRANCH;
            $modelData['shop_id'] = $parent->shop_id;
        } else {
            $modelData['type'] = DepartmentTypeEnum::ROOT;
            $modelData['shop_id'] = $parent->id;
        }

        /** @var Department $department */
        $department = $parent->departments()->create($modelData);

        $department->stats()->create();
        $department->salesStats()->create([
            'scope' => 'sales'
        ]);
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');
        if ($department->shop->currency_id != $tenant->currency_id) {
            $department->salesStats()->create([
                'scope' => 'sales-tenant-currency'
            ]);
        }

        DepartmentHydrateUniversalSearch::dispatch($department);
        ShopHydrateDepartments::dispatch($department->shop);

        return $department;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:tenant.departments', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'image_id' => ['sometimes', 'required', 'exists:media,id'],
            'state' => ['sometimes', 'required'],
            'description' => ['sometimes', 'required', 'max:1500'],
        ];
    }

    public function action(Shop|Department $parent, array $objectData): Department
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
