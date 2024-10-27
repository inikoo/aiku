<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrderTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionDeliveryStatusEnum;
use App\Models\Inventory\OrgStock;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SupplyChain\HistoricSupplierProduct;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePurchaseOrderTransaction extends OrgAction
{
    use WithNoStrictRules;

    public function handle(PurchaseOrder $purchaseOrder, HistoricSupplierProduct|OrgStock $item, array $modelData): PurchaseOrderTransaction
    {
        data_set($modelData, 'group_id', $purchaseOrder->group_id);
        data_set($modelData, 'organisation_id', $purchaseOrder->organisation_id);


        if (class_basename($item) == 'HistoricSupplierProduct') {
            $supplierProduct = $item->supplierProduct;
            data_set($modelData, 'supplier_product_id', $supplierProduct->id);
            data_set($modelData, 'historic_supplier_product_id', $item->id);
            $orgSupplierProduct = $supplierProduct->orgSupplierProducts()->where('organisation_id', $purchaseOrder->organisation_id)->first();

            data_set($modelData, 'org_supplier_product_id', $orgSupplierProduct->id);
            $orgStock = $supplierProduct->stock->orgStocks()->where('organisation_id', $purchaseOrder->organisation_id)->first();
            data_set($modelData, 'stock_id', $supplierProduct->stock->id);
            data_set($modelData, 'org_stock_id', $orgStock->id);
        } else {
            data_set($modelData, 'org_stock_id', $item->id);
            data_set($modelData, 'stock_id', $item->stock_id);
        }

        data_set($modelData, 'org_exchange', $purchaseOrder->org_exchange, overwrite: false);
        data_set($modelData, 'grp_exchange', $purchaseOrder->grp_exchange, overwrite: false);


        /** @var PurchaseOrderTransaction $purchaseOrderTransaction */
        $purchaseOrderTransaction = $purchaseOrder->purchaseOrderTransactions()->create($modelData);

        return $purchaseOrderTransaction;
    }

    public function rules(): array
    {
        $rules = [
            'quantity_ordered' => ['required', 'numeric', 'min:0'],

            'state'           => ['sometimes','required', Rule::enum(PurchaseOrderTransactionStateEnum::class)],
            'delivery_status' => ['sometimes','required', Rule::enum(PurchaseOrderTransactionDeliveryStatusEnum::class)],
            'gross_amount'    => ['sometimes', 'numeric'],
            'net_amount'      => ['sometimes', 'numeric'],
            'org_exchange'    => ['sometimes', 'numeric'],
            'grp_exchange'    => ['sometimes', 'numeric'],
            'org_net_amount'  => ['sometimes', 'numeric'],
            'grp_net_amount'  => ['sometimes', 'numeric'],
            'date'            => ['sometimes', 'required', 'date'],
            'submitted_at'    => ['sometimes', 'required', 'date'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }


        return $rules;
    }



    public function action(PurchaseOrder $purchaseOrder, HistoricSupplierProduct|OrgStock $item, array $modelData, bool $strict = true): PurchaseOrderTransaction
    {
        $this->strict = $strict;
        $this->initialisation($purchaseOrder->organisation, $modelData);

        return $this->handle($purchaseOrder, $item, $this->validatedData);
    }

    public function asController(PurchaseOrder $purchaseOrder, HistoricSupplierProduct $historicSupplierProduct, ActionRequest $request): void
    {
        $this->initialisation($purchaseOrder->organisation, $request);
        $this->handle($purchaseOrder, $historicSupplierProduct, $this->validatedData);
    }
}
