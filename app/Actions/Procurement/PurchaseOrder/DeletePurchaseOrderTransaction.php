<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 16:55:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\Hydrators\PurchaseOrderHydrateTransactions;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\PurchaseOrder;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\ShoppingListItem\ShoppingListItemStateEnum;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\Procurement\ShoppingListItem;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Validation\Validator;

class DeletePurchaseOrderTransaction extends OrgAction
{
    use WithProcurementEditAuthorisation;
    use AsAction;
    use WithAttributes;

    private PurchaseOrderTransaction $purchaseOrderTransaction;

    public function afterValidator(Validator $validator): void
    {
        if ($this->asAction) {
            return;
        }

        $state = $this->purchaseOrderTransaction->purchaseOrder->state;
        if ($state !== PurchaseOrderStateEnum::IN_PROCESS) {
            $validator->errors()->add('state', __('You can not delete a line of a purchase order with state :state', ['state' => $state->value]));
        }
    }

    public function handle(PurchaseOrderTransaction $purchaseOrderTransaction): void
    {
        $purchaseOrder = $purchaseOrderTransaction->purchaseOrder;
        self::reopenShoppingListItems([$purchaseOrderTransaction->id]);
        $purchaseOrderTransaction->delete();
        CalculatePurchaseOrderTotalAmounts::run($purchaseOrder);
        PurchaseOrderHydrateTransactions::dispatch($purchaseOrder);
    }

    public static function reopenShoppingListItems(array $purchaseOrderTransactionIds): void
    {
        ShoppingListItem::withTrashed()->whereIn('purchase_order_transaction_id', $purchaseOrderTransactionIds)->update([
            'purchase_order_transaction_id' => null,
            'state'                         => ShoppingListItemStateEnum::OPEN,
        ]);
    }

    public function action(PurchaseOrderTransaction $purchaseOrderTransaction): void
    {
        $this->asAction = true;
        $this->initialisation($purchaseOrderTransaction->organisation, []);
        $this->handle($purchaseOrderTransaction);
    }

    public function asController(PurchaseOrder $purchaseOrder, PurchaseOrderTransaction $purchaseOrderTransaction, ActionRequest $request): void
    {
        $this->purchaseOrderTransaction = $purchaseOrderTransaction;
        $this->initialisation($purchaseOrderTransaction->organisation, $request);
        $this->handle($purchaseOrderTransaction);
    }
}
