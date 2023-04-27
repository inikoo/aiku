<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Actions\Marketing\Department\Hydrators\DepartmentHydrateUniversalSearch;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateDepartments;
use App\Enums\Marketing\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreDepartment
{
    use AsAction;
    use WithAttributes;

    public function handle(Shop|ProductCategory $parent, array $modelData): ProductCategory
    {
        if (class_basename($parent) == 'Department') {
            $modelData['type']    = ProductCategoryTypeEnum::BRANCH;
            $modelData['shop_id'] = $parent->shop_id;
        } else {
            $modelData['type']    = ProductCategoryTypeEnum::ROOT;
            $modelData['shop_id'] = $parent->id;
        }

        /** @var ProductCategory $productCategory */
        $productCategory = $parent->departments()->create($modelData);

        $productCategory->stats()->create();
        $productCategory->salesStats()->create([
            'scope' => 'sales'
        ]);
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');
        if ($productCategory->shop->currency_id != $tenant->currency_id) {
            $productCategory->salesStats()->create([
                'scope' => 'sales-tenant-currency'
            ]);
        }

        DepartmentHydrateUniversalSearch::dispatch($productCategory);
        ShopHydrateDepartments::dispatch($productCategory->shop);

        return $productCategory;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:tenant.product_categories', 'between:2,9', 'alpha'],
            'name'        => ['required', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'state'       => ['sometimes', 'required'],
            'description' => ['sometimes', 'required', 'max:1500'],
        ];
    }

    public function action(Shop|ProductCategory $parent, array $objectData): ProductCategory
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
