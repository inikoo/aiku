<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:31:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory;

use App\Actions\Market\ProductCategory\Hydrators\ProductCategoryHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\ProductCategory\ProductCategoryStateEnum;
use App\Http\Resources\Market\DepartmentsResource;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProductCategory extends OrgAction
{
    use WithActionUpdate;

    private ProductCategory $productCategory;

    public function handle(ProductCategory $productCategory, array $modelData): ProductCategory
    {
        $productCategory = $this->update($productCategory, $modelData, ['data']);
        ProductCategoryHydrateUniversalSearch::dispatch($productCategory);

        return $productCategory;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'sometimes',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'product_categories',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'type', 'value' => $this->productCategory->type, 'operator' => '='],
                        ['column' => 'id', 'value' => $this->productCategory->id, 'operator' => '!=']

                    ]
                ),
            ],
            'name'        => ['sometimes', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'state'       => ['sometimes', 'required', Rule::enum(ProductCategoryStateEnum::class)],
            'description' => ['sometimes', 'required', 'max:1500'],
            'created_at'  => ['sometimes', 'date'], // todo delete this after all fetching from aurora is done

        ];
    }

    public function action(ProductCategory $productCategory, array $modelData): ProductCategory
    {
        $this->asAction        = true;
        $this->productCategory = $productCategory;
        $this->initialisationFromShop($productCategory->shop, $modelData);

        return $this->handle($productCategory, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, ProductCategory $productCategory, ActionRequest $request): ProductCategory
    {
        $this->productCategory = $productCategory;

        $this->initialisationFromShop($shop, $request);

        return $this->handle($productCategory, $this->validatedData);
    }

    public function jsonResponse(ProductCategory $productCategory): DepartmentsResource
    {
        return new DepartmentsResource($productCategory);
    }
}
