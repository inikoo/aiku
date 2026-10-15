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
     * Takes back one deposit instalment. Its bank statement stays on the Attachments tab.
     */
    public function handle(PurchaseOrder $purchaseOrder, PurchaseOrderDepositPayment $depositPayment): PurchaseOrder
    {
        $depositPayment->delete();

        return SyncPurchaseOrderDepositPaid::run($purchaseOrder);
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

        return $this->handle($purchaseOrder, $depositPayment);
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
