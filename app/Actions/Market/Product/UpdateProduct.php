<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:09:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Http\Resources\Market\ProductResource;
use App\Models\Market\Product;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProduct extends OrgAction
{
    use WithActionUpdate;

    private Product $product;

    public function handle(Product $product, array $modelData, bool $skipHistoric = false): Product
    {
        $product = $this->update($product, $modelData, ['data', 'settings']);
        if (!$skipHistoric and $product->wasChanged(
            ['price', 'code', 'name', 'units']
        )) {
            //todo create HistoricOuter and update current_historic_outer_id if
        }
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
                        ['column' => 'deleted_at', 'value' => null],
                        ['column' => 'id', 'value' => $this->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'type'        => ['sometimes', 'required', Rule::enum(ProductTypeEnum::class)],
        ];
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $this->validatedData);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0, $skipHistoric = false): Product
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->product        = $product;

        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData, $skipHistoric);
    }

    public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }
}
