<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 06:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderDepositPayment;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class UndoPurchaseOrderPayment extends OrgAction
{
    private PurchaseOrder $purchaseOrder;

    /**
     * Takes back one deposit instalment, keeping why and who on it and in the order's history. Its bank statement
     * stays on the Attachments tab.
     */
    public function handle(PurchaseOrder $purchaseOrder, PurchaseOrderDepositPayment $depositPayment, ?string $reason = null, ?int $userId = null): PurchaseOrder
    {
        $depositPayment->update(['undo_reason' => $reason, 'undone_by_id' => $userId]);
        $depositPayment->delete();

        if ($reason) {
            MarkPurchaseOrderStage::auditOverride($purchaseOrder, 'deposit_paid_at', $depositPayment->date->toDateString(), null, $reason);
        }

        return SyncPurchaseOrderDepositPaid::run($purchaseOrder);
    }

    public function rules(): array
    {
        return [
            'reason' => [$this->asAction ? 'sometimes' : 'required', 'nullable', 'string', 'max:500'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->asAction || ConfirmPurchaseOrderPayment::canConfirm($this->purchaseOrder, $request->user());
    }

    public function asController(PurchaseOrder $purchaseOrder, PurchaseOrderDepositPayment $depositPayment, ActionRequest $request): PurchaseOrder
    {
        abort_unless($depositPayment->purchase_order_id === $purchaseOrder->id, 404);
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, $depositPayment, trim($this->validatedData['reason']), $request->user()->id);
    }

    public function action(PurchaseOrder $purchaseOrder, PurchaseOrderDepositPayment $depositPayment): PurchaseOrder
    {
        $this->asAction      = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, []);

        return $this->handle($purchaseOrder, $depositPayment);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
