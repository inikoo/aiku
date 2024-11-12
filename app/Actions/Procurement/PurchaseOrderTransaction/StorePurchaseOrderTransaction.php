<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrderTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithStoreProcurementOrderItem;
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
    use WithStoreProcurementOrderItem;

    public function handle(PurchaseOrder $purchaseOrder, HistoricSupplierProduct|OrgStock $item, array $modelData): PurchaseOrderTransaction
    {
        $modelData = $this->prepareProcurementOrderItem($purchaseOrder, $item, $modelData);

        /** @var PurchaseOrderTransaction $purchaseOrderTransaction */
        $purchaseOrderTransaction = $purchaseOrder->purchaseOrderTransactions()->create($modelData);

        return $purchaseOrderTransaction;
    }

    public function rules(): array
    {
        $rules = [
            'quantity_ordered' => ['required', 'numeric', 'min:0'],
        ];

        if (!$this->strict) {
            $rules['state']           = ['sometimes', 'required', Rule::enum(PurchaseOrderTransactionStateEnum::class)];
            $rules['delivery_status'] = ['sometimes', 'required', Rule::enum(PurchaseOrderTransactionDeliveryStatusEnum::class)];
            $rules['submitted_at']    = ['sometimes', 'required', 'date'];
            $rules['net_amount']   = ['sometimes', 'numeric'];
            $rules['org_exchange'] = ['sometimes', 'numeric'];
            $rules['grp_exchange'] = ['sometimes', 'numeric'];


            $rules = $this->noStrictStoreRules($rules);
        }


        return $rules;
    }

    public function action(PurchaseOrder $purchaseOrder, HistoricSupplierProduct|OrgStock $item, array $modelData, int $hydratorsDelay = 0, bool $strict = true): PurchaseOrderTransaction
    {
        $this->asAction       = true;
        $this->strict = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($purchaseOrder->organisation, $modelData);

        return $this->handle($purchaseOrder, $item, $this->validatedData);
    }

    public function asController(PurchaseOrder $purchaseOrder, HistoricSupplierProduct $historicSupplierProduct, ActionRequest $request): void
    {
        $this->initialisation($purchaseOrder->organisation, $request);
        $this->handle($purchaseOrder, $historicSupplierProduct, $this->validatedData);
    }
}
