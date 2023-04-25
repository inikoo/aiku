<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 22:27:24 Malaysia Time, Plane Bali - KL
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Procurement\SupplierProduct;

class UpdateSupplierProduct
{
    use WithActionUpdate;

    public function handle(SupplierProduct $supplierProduct, array $modelData, bool $skipHistoric=false): SupplierProduct
    {
        $supplierProduct= $this->update($supplierProduct, $modelData, ['data', 'settings']);
        if (!$skipHistoric and $supplierProduct->wasChanged(
            ['price', 'code','name','units']
        )) {
            //todo create HistoricSupplierProduct and update current_historic_product_id if
        }

        SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);
        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:group.supplier_products', 'between:2,9', 'alpha'],
            'name' => ['required', 'max:250', 'string'],
            'cost' => ['required'],
        ];
    }

    public function action(SupplierProduct $supplierProduct, array $objectData, bool $skipHistoric = false): SupplierProduct
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($supplierProduct, $validatedData, $skipHistoric);
    }
}
