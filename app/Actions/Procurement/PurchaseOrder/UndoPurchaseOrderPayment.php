<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 06:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class UndoPurchaseOrderPayment extends OrgAction
{
    use WithActionUpdate;

    private PurchaseOrder $purchaseOrder;

    /**
     * The bank statement stays on the Attachments tab; only the confirmation is taken back.
     *
     */
    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $this->update($purchaseOrder, [
            'deposit_paid_at'    => null,
            'deposit_paid_by_id' => null,
            'data'               => ['payments' => ['deposit' => ['method' => null, 'reference' => null]]],
        ], ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->asAction || ConfirmPurchaseOrderPayment::canConfirm($this->purchaseOrder, $request->user());
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder);
    }

    public function action(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->asAction      = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, []);

        return $this->handle($purchaseOrder);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
