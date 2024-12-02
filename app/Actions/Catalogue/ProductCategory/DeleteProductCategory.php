<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\OrgAction;
use App\Models\Catalogue\ProductCategory;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Validation\Validator;

class DeleteProductCategory extends OrgAction
{
    private ProductCategory $productCategory;

    public function handle(ProductCategory $productCategory): ProductCategory
    {
        $productCategory->stats()->delete();
        $productCategory->delete();
        return $productCategory;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($this->productCategory->products()->exists()) {
            $validator->errors()->add('products', 'This category has products associated with it.');
        }

        if ($this->productCategory->children()->exists()) {
            $validator->errors()->add('children', 'This category has sub-categories associated with it.');
        }

    }

    public function asController(ProductCategory $productCategory, ActionRequest $request): ProductCategory
    {
        $this->productCategory = $productCategory;
        $this->initialisationFromShop($productCategory->shop, $request);

        return $this->handle($productCategory);
    }



}
