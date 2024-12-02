<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 22 Sept 2024 17:47:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\Catalogue\ProductCategory\Hydrators\DepartmentHydrateSubDepartments;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateFamilies;
use App\Actions\Catalogue\ProductCategory\Hydrators\SubDepartmentHydrateSubDepartments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSubDepartments;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateDepartments;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateFamilies;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSubDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFamilies;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSubDepartments;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;

trait WithProductCategoryHydrators
{
    protected function productCategoryHydrators(ProductCategory $productCategory)
    {
        switch ($productCategory->type) {
            case ProductCategoryTypeEnum::DEPARTMENT:
                GroupHydrateDepartments::dispatch($productCategory->group)->delay($this->hydratorsDelay);
                OrganisationHydrateDepartments::dispatch($productCategory->organisation)->delay($this->hydratorsDelay);
                ShopHydrateDepartments::dispatch($productCategory->shop)->delay($this->hydratorsDelay);

                break;
            case ProductCategoryTypeEnum::FAMILY:
                GroupHydrateFamilies::dispatch($productCategory->group)->delay($this->hydratorsDelay);
                OrganisationHydrateFamilies::dispatch($productCategory->organisation)->delay($this->hydratorsDelay);
                ShopHydrateFamilies::dispatch($productCategory->shop)->delay($this->hydratorsDelay);

                if ($productCategory->parent_id) {
                    ProductCategoryHydrateFamilies::dispatch($productCategory->parent)->delay($this->hydratorsDelay);
                }
                break;
            case ProductCategoryTypeEnum::SUB_DEPARTMENT:
                GroupHydrateSubDepartments::dispatch($productCategory->group)->delay($this->hydratorsDelay);
                OrganisationHydrateSubDepartments::dispatch($productCategory->organisation)->delay($this->hydratorsDelay);
                ShopHydrateSubDepartments::dispatch($productCategory->shop)->delay($this->hydratorsDelay);

                if ($productCategory->department_id) {
                    DepartmentHydrateSubDepartments::dispatch($productCategory->department)->delay($this->hydratorsDelay);
                }
                if ($productCategory->sub_department_id) {
                    SubDepartmentHydrateSubDepartments::dispatch($productCategory->subDepartment)->delay($this->hydratorsDelay);
                }


                break;
        }
    }
}
