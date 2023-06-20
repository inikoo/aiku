<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:09:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\Product;
use Lorisleiva\Actions\ActionRequest;

class UpdateProduct
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(Product $product, array $modelData, bool $skipHistoric=false): Product
    {
        $product= $this->update($product, $modelData, ['data', 'settings']);
        if (!$skipHistoric and $product->wasChanged(
            ['price', 'code','name','units']
        )) {
            //todo create HistoricProduct and update current_historic_product_id if
        }
        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.products.edit");
    }
    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:tenant.products', 'between:2,9', 'alpha'],
            'units'       => ['sometimes', 'required', 'numeric'],
            'price'       => ['sometimes', 'required', 'numeric'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],
            'description' => ['sometimes', 'required', 'max:1500'],
        ];
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $request->validate();
        return $this->handle($product, $request->all());
    }

    public function action(Product $product, array $objectData): Product
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($product, $validatedData);
    }

    public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }
}
