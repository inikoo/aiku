<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 08:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class SetPurchaseOrderDeposit extends OrgAction
{
    use WithActionUpdate;

    private PurchaseOrder $purchaseOrder;

    /**
     * An amount of zero means no deposit is needed; null means nobody has decided yet.
     *
     * @param  array{amount: float|string|null}  $modelData
     */
    public function handle(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        return $this->update($purchaseOrder, ['deposit_amount' => $modelData['amount']]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->asAction || ConfirmPurchaseOrderPayment::canConfirm($this->purchaseOrder, $request->user());
    }

    public function rules(): array
    {
        return [
            'amount' => ['present', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!ConfirmPurchaseOrderPayment::acceptsPayments($this->purchaseOrder)) {
            $validator->errors()->add('amount', __('A payment can only be confirmed once the purchase order is submitted'));
        } elseif ($this->purchaseOrder->deposit_paid_at) {
            $validator->errors()->add('amount', __('The deposit is already paid: undo the payment to change it'));
        } elseif ((float) $this->get('amount') > (float) $this->purchaseOrder->cost_total) {
            $validator->errors()->add('amount', __('The deposit cannot be more than the total of the purchase order'));
        }
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, $this->validatedData);
    }

    public function action(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        $this->asAction      = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $modelData);

        return $this->handle($purchaseOrder, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
