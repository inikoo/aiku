<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:09:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Outer\UpdateOuter;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Http\Resources\Catalogue\ProductResource;
use App\Models\Catalogue\Product;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePhysicalGood extends OrgAction
{
    use WithActionUpdate;

    private Product $product;

    public function handle(Product $product, array $modelData): Product
    {


        $mainOuterData= Arr::only($modelData, ['name','code','price']);
        UpdateOuter::run($product->mainOuterable, $mainOuterData);
        $modelData=Arr::except($modelData, ['name','code','price']);

        $product = $this->update($product, $modelData, ['data', 'settings']);
        $product->refresh();



        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.products.edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'sometimes',
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'status'      => ['sometimes', 'required', 'boolean'],
            'state'       => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
        ];
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $this->validatedData);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Product
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->product        = $product;

        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }

    public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }
}
