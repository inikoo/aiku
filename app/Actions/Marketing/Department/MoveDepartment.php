<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 16:51:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\ProductCategory;
use Lorisleiva\Actions\ActionRequest;

class MoveDepartment
{
    use WithActionUpdate;


    private bool $asAction=false;

    public function handle(ProductCategory $productCategory, array $modelData): ProductCategory
    {
        $productCategory = $this->update($productCategory, $modelData, ['data']);

        return $productCategory;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.products.edit");
    }

    public function action(ProductCategory $productCategory, array $objectData): ProductCategory
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($productCategory, $validatedData);
    }
}
