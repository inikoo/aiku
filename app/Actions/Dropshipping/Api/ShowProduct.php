<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 13:36:08 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Api;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\ProductsResource;
use App\Http\Resources\Catalogue\ProductResource;
use App\Models\Catalogue\Product;
use Lorisleiva\Actions\ActionRequest;

class ShowProduct extends GrpAction
{
    private Product $product;

    public function asController(Product $product, ActionRequest $request): ProductResource
    {
        $group         = $request->user();
        $this->product =$product;
        $this->initialisation($group, $request);

        return $this->handle($product);
    }

    public function handle(Product $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function prepareForValidation(): void
    {
        if($this->product->shop->type!=ShopTypeEnum::DROPSHIPPING) {
            abort(404);
        }

    }


    public function jsonResponse($product): ProductsResource
    {
        return ProductsResource::make($product);
    }

}
