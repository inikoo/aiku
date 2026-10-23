<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrderTransaction;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\NotifyBuyerOfAgentStageChange;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionQuantityQueryStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent asks the buying organisation to change the quantity ordered on a line, before confirming the order.
 */
class AskPurchaseOrderTransactionQuantityChange extends OrgAction
{
    use WithActionUpdate;

    private PurchaseOrderTransaction $purchaseOrderTransaction;

    public function handle(PurchaseOrderTransaction $purchaseOrderTransaction, array $modelData, ?User $user = null): PurchaseOrderTransaction
    {
        return $this->update($purchaseOrderTransaction, [
            'quantity_query_state'             => PurchaseOrderTransactionQuantityQueryStateEnum::PENDING,
            'quantity_query_quantity'          => round((float) $modelData['quantity_ordered'], 3),
            'quantity_query_previous_quantity' => $purchaseOrderTransaction->quantity_ordered,
            'quantity_query_note'              => $modelData['note'] ?? null,
            'quantity_queried_by_id'           => $user?->id,
            'quantity_queried_at'              => now(),
            'quantity_query_answered_at'       => null,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $purchaseOrder       = $this->purchaseOrderTransaction->purchaseOrder;
        $agentOrganisationId = $purchaseOrder->isAgentOrder() ? $purchaseOrder->agent?->organisation_id : null;

        return $agentOrganisationId !== null && $request->user()->authTo("procurement.$agentOrganisationId.edit");
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->purchaseOrderTransaction->purchaseOrder->state !== PurchaseOrderStateEnum::SUBMITTED
            || $this->purchaseOrderTransaction->state !== PurchaseOrderTransactionStateEnum::SUBMITTED) {
            $validator->errors()->add('quantity_ordered', __('A new quantity can only be asked for while the order waits for the agent to confirm it'));
        }
    }

    public function rules(): array
    {
        return [
            'quantity_ordered' => ['required', 'numeric', 'gt:0'],
            'note'             => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function action(PurchaseOrderTransaction $purchaseOrderTransaction, array $modelData, ?User $user = null): PurchaseOrderTransaction
    {
        $this->asAction                 = true;
        $this->purchaseOrderTransaction = $purchaseOrderTransaction;
        $this->initialisation($purchaseOrderTransaction->organisation, $modelData);

        return $this->handle($purchaseOrderTransaction, $this->validatedData, $user);
    }

    public function asController(PurchaseOrder $purchaseOrder, PurchaseOrderTransaction $purchaseOrderTransaction, ActionRequest $request): RedirectResponse
    {
        abort_unless($purchaseOrderTransaction->purchase_order_id === $purchaseOrder->id, 404);
        $this->purchaseOrderTransaction = $purchaseOrderTransaction;
        $this->initialisation($purchaseOrderTransaction->organisation, $request);

        $purchaseOrderTransaction = $this->handle($purchaseOrderTransaction, $this->validatedData, $request->user());

        NotifyBuyerOfAgentStageChange::run($purchaseOrder, $request->user(), __('the agent asks to order :new units of :code, was :old', [
            'code' => $purchaseOrderTransaction->supplierProduct?->code ?? $purchaseOrderTransaction->orgStock?->code,
            'new'  => trimDecimalZeros($purchaseOrderTransaction->quantity_query_quantity),
            'old'  => trimDecimalZeros($purchaseOrderTransaction->quantity_query_previous_quantity),
        ]));

        return back();
    }
}
