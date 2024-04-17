<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory;

use App\Actions\Market\ProductCategory\Hydrators\ProductCategoryHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Market\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFamilies;
use App\Enums\Market\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Market\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreProductCategory extends OrgAction
{
    public function handle(Shop|ProductCategory $parent, array $modelData): ProductCategory
    {
        if (class_basename($parent) == 'ProductCategory') {
            $modelData['shop_id'] = $parent->shop_id;
        } else {
            $modelData['shop_id'] = $parent->id;
        }
        data_set($modelData, 'parent_id', $parent->id);
        data_set($modelData, 'parent_type', class_basename($parent));
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);


        /** @var ProductCategory $productCategory */
        $productCategory = ProductCategory::create($modelData);

        $productCategory->stats()->create();

        ProductCategoryHydrateUniversalSearch::dispatch($productCategory);

        switch ($productCategory->type) {
            case ProductCategoryTypeEnum::DEPARTMENT:
                OrganisationHydrateDepartments::dispatch($productCategory->organisation)->delay($this->hydratorsDelay);
                ShopHydrateDepartments::dispatch($productCategory->shop)->delay($this->hydratorsDelay);
                break;
            case ProductCategoryTypeEnum::FAMILY:
                OrganisationHydrateFamilies::dispatch($productCategory->organisation)->delay($this->hydratorsDelay);
                ShopHydrateFamilies::dispatch($productCategory->shop)->delay($this->hydratorsDelay);
                break;
        }



        return $productCategory;
    }

    public function rules(): array
    {
        return [
            'type'                 => ['required', Rule::enum(ProductCategoryTypeEnum::class)],
            'code'                 => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'product_categories',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'value' => null],
                    ]
                ),
            ],
            'name'                 => ['required', 'max:250', 'string'],
            'image_id'             => ['sometimes', 'required', 'exists:media,id'],
            'state'                => ['sometimes', Rule::enum(ProductCategoryStateEnum::class)],
            'description'          => ['sometimes', 'required', 'max:1500'],
            'created_at'           => ['sometimes', 'date'],
            'source_department_id' => ['sometimes', 'string', 'max:255'],
            'source_family_id'     => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0): ProductCategory
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        if ($parent instanceof Shop) {
            $shop = $parent;
        } else {
            $shop = $parent->shop;
        }

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ProductCategory
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }


}
