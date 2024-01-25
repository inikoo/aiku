<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory;

use App\Actions\Market\ProductCategory\Hydrators\ProductCategoryHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\OrgAction;
use App\Enums\Market\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreProductCategory extends OrgAction
{
    public function handle(Shop|ProductCategory $parent, array $modelData): ProductCategory
    {
        if (class_basename($parent) == 'ProductCategory') {
            $modelData['type']    = ProductCategoryTypeEnum::BRANCH;
            $modelData['shop_id'] = $parent->shop_id;
        } else {
            $modelData['type']    = ProductCategoryTypeEnum::ROOT;
            $modelData['shop_id'] = $parent->id;
        }

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation', $parent->organisation_id);


        /** @var ProductCategory $productCategory */
        $productCategory = $parent->departments()->create($modelData);

        $productCategory->stats()->create();
        $productCategory->salesStats()->create([
            'scope' => 'sales'
        ]);

        if ($productCategory->shop->currency_id != $parent->organisation->currency_id) {
            $productCategory->salesStats()->create([
                'scope' => 'sales-tenant-currency'
            ]);
        }

        ProductCategoryHydrateUniversalSearch::dispatch($productCategory);
        ShopHydrateDepartments::dispatch($productCategory->shop);

        return $productCategory;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:product_categories', 'between:2,9', 'alpha_dash'],
            'name'        => ['required', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'state'       => ['sometimes', 'required'],
            'description' => ['sometimes', 'required', 'max:1500'],
        ];
    }

    public function action(Shop|ProductCategory $parent, array $modelData, int $hydratorsDelay = 0): ProductCategory
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($parent->organisation, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ProductCategory
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }


}
