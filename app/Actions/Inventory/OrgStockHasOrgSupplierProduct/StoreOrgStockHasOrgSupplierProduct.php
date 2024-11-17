<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockHasOrgSupplierProduct;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockHasOrgSupplierProduct;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\StockHasSupplierProduct;
use Illuminate\Validation\Validator;

class StoreOrgStockHasOrgSupplierProduct extends OrgAction
{
    use WithNoStrictRules;


    private StockHasSupplierProduct $stockHasSupplierProduct;
    private OrgStock $orgStock;
    private OrgSupplierProduct $orgSupplierProduct;

    public function handle(StockHasSupplierProduct $stockHasSupplierProduct, OrgStock $orgStock, OrgSupplierProduct $orgSupplierProduct, array $modelData): OrgStockHasOrgSupplierProduct
    {

        data_set($modelData, 'org_stock_id', $orgStock->id);
        data_set($modelData, 'org_supplier_product_id', $orgSupplierProduct->id);


        // dd($stockHasSupplierProduct);

        return $stockHasSupplierProduct->orgStockHasOrgSupplierProducts()->create($modelData);
    }

    public function rules(): array
    {
        $rules = [
            'status'                  => ['sometimes', 'boolean'],
            'local_priority'          => ['sometimes', 'integer'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->orgStock->stock != $this->stockHasSupplierProduct->stock) {
            $validator->errors()->add('org_stock_id', 'Stock does not belong to this supplier product');
        }
        if ($this->orgSupplierProduct->supplierProduct != $this->stockHasSupplierProduct->supplierProduct) {
            $validator->errors()->add('org_supplier_product_id', 'Supplier Product does not belong to this stock');
        }
    }


    public function action(StockHasSupplierProduct $stockHasSupplierProduct, OrgStock $orgStock, OrgSupplierProduct $orgSupplierProduct, array $modelData, bool $strict = true): OrgStockHasOrgSupplierProduct
    {
        $this->asAction = true;
        $this->strict   = $strict;

        $this->stockHasSupplierProduct = $stockHasSupplierProduct;
        $this->orgStock = $orgStock;
        $this->orgSupplierProduct = $orgSupplierProduct;

        $this->initialisation($orgStock->organisation, $modelData);

        return $this->handle($stockHasSupplierProduct, $orgStock, $orgSupplierProduct, $this->validatedData);
    }


}
