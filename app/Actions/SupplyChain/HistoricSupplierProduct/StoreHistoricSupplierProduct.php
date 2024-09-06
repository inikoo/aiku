<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\HistoricSupplierProduct;

use App\Actions\GrpAction;
use App\Models\SupplyChain\HistoricSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricSupplierProduct extends GrpAction
{
    use AsAction;

    public function handle(SupplierProduct $supplierProduct, array $modelData = []): HistoricSupplierProduct
    {
        data_set($modelData, 'group_id', $supplierProduct->group_id);
        data_set($modelData, 'code', $supplierProduct->code, overwrite: false);
        data_set($modelData, 'name', $supplierProduct->name, overwrite: false);
        data_set($modelData, 'cost', $supplierProduct->cost, overwrite: false);
        data_set($modelData, 'units_per_pack', $supplierProduct->units_per_pack, overwrite: false);
        data_set($modelData, 'units_per_carton', $supplierProduct->units_per_carton, overwrite: false);
        data_set($modelData, 'cbm', $supplierProduct->cbm, overwrite: false);
        data_set($modelData, 'currency_id', $supplierProduct->currency_id, overwrite: false);



        /** @var HistoricSupplierProduct $historicSupplierProduct */
        $historicSupplierProduct = $supplierProduct->historicSupplierProducts()->create($modelData);
        $historicSupplierProduct->stats()->create();

        return $historicSupplierProduct;
    }

    public function rules(): array
    {
        $rules = [
            'code'             => ['sometimes','required', 'string', 'max:255'],
            'name'             => ['sometimes','required', 'string', 'max:255'],
            'status'           => ['required', 'boolean'],
            'cost'             => ['sometimes','required', 'numeric'],
            'units_per_pack'   => ['sometimes','required', 'numeric'],
            'units_per_carton' => ['sometimes','required', 'numeric'],
            'cbm'              => ['sometimes','required', 'numeric'],
            'currency_id'      => ['sometimes','required', 'exists:currencies,id'],


        ];


        if (!$this->strict) {
            $rules['fetched_at'] = ['sometimes', 'date'];
            $rules['source_id']  = ['sometimes', 'string', 'max:255'];
            $rules['created_at'] = ['sometimes', 'date'];
            $rules['deleted_at'] = ['sometimes', 'date'];
        }

        return $rules;
    }

    public function action(SupplierProduct $supplierProduct, array $modelData, int $hydratorsDelay = 0, bool $strict = true): HistoricSupplierProduct
    {
        $this->strict         = $strict;
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($supplierProduct->group, $modelData);

        return $this->handle($supplierProduct, $this->validatedData);
    }
}
