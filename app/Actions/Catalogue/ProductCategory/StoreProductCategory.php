<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFamilies;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreProductCategory extends OrgAction
{
    public function handle(Shop|ProductCategory $parent, array $modelData): ProductCategory
    {
        if (class_basename($parent) == 'ProductCategory') {
            $modelData['shop_id']             = $parent->shop_id;
            $modelData['product_category_id'] = $parent->id;
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
        $productCategory->salesIntervals()->create();

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
            case ProductCategoryTypeEnum::SUB_DEPARTMENT:
                //OrganisationHydrateSubDepartments::dispatch($productCategory->organisation)->delay($this->hydratorsDelay);
                //ShopHydrateFamilies::dispatch($productCategory->shop)->delay($this->hydratorsDelay);
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
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
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
            'department_id'        => [
                'sometimes','nullable',
                Rule::Exists('product_categories', 'id')->where('shop_id', $this->shop->id)->where('type', ProductCategoryTypeEnum::DEPARTMENT)
            ]
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $productCategory, ActionRequest $request): ProductCategory
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $productCategory, modelData: $this->validatedData);
    }

    public function htmlResponse(ProductCategory $productCategory, ActionRequest $request): RedirectResponse
    {
        if (class_basename($productCategory->parent) == 'ProductCategory') {
            return Redirect::route('grp.org.shops.show.catalogue.departments.show.families.show', [
                'organisation' => $productCategory->organisation->slug,
                'shop'         => $productCategory->shop->slug,
                'department'   => $productCategory->parent->slug,
                'family'       => $productCategory->slug,
            ]);
        } else {
            abort(419);
        }
    }


}
