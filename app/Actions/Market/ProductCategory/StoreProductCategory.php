<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:27:33 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory;

use App\Actions\Market\ProductCategory\Hydrators\ProductCategoryHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateDepartments;
use App\Enums\Market\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProductCategory
{
    use AsAction;
    use WithAttributes;

    private int $hydratorsDelay =0;

    public function handle(Shop|ProductCategory $parent, array $modelData): ProductCategory
    {
        if (class_basename($parent) == 'ProductCategory') {
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
        /** @var Organisation $organisation */
        $organisation = app('currentTenant');
        if ($productCategory->shop->currency_id != $organisation->currency_id) {
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
            'code'        => ['required', 'unique:product_categories', 'between:2,9', 'alpha_dash', new CaseSensitive('product_categories')],
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

    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $this->handle($shop, $request->all());
        return  Redirect::route('grp.shops.show.departments.index', $shop);
    }

    public function asFetch(Shop $shop, array $productCategoryData, int $hydratorsDelay=60): ProductCategory
    {
        $this->hydratorsDelay=$hydratorsDelay;
        return $this->handle($shop, $productCategoryData);
    }
}
