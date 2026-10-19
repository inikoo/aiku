<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\AgentPayment;

use App\Actions\OrgAction;
use App\Enums\SupplyChain\AgentPayment\AgentPaymentTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\AgentPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent corrects a line of a client's ledger (its date, type, order or container) and so takes it off the
 * list of lines to review. The amount stays as recorded: a wrong amount is removed and recorded again.
 */
class UpdateAgentLedgerEntry extends OrgAction
{
    private AgentPayment $agentPayment;

    /**
     * @param  array{date: string, type: string, purchase_order_id?: int|null, stock_delivery_id?: int|null, reference?: string|null, notes?: string|null}  $modelData
     */
    public function handle(AgentPayment $agentPayment, array $modelData): AgentPayment
    {
        $purchaseOrder = ($purchaseOrderId = Arr::get($modelData, 'purchase_order_id')) ? PurchaseOrder::findOrFail($purchaseOrderId) : null;

        $agentPayment->update([
            'date'              => $modelData['date'],
            'type'              => AgentPaymentTypeEnum::from($modelData['type']),
            'purchase_order_id' => $purchaseOrder?->id,
            'supplier_id'       => $purchaseOrder?->supplier_id,
            'stock_delivery_id' => Arr::get($modelData, 'stock_delivery_id'),
            'reference'         => Arr::get($modelData, 'reference'),
            'notes'             => Arr::get($modelData, 'notes'),
            'needs_review'      => false,
            'review_reason'     => null,
        ]);

        return $agentPayment;
    }

    public function rules(): array
    {
        $supplierPayments = implode(',', array_map(fn (AgentPaymentTypeEnum $type) => $type->value, array_filter(AgentPaymentTypeEnum::cases(), fn (AgentPaymentTypeEnum $type) => $type->isSupplierPayment())));

        return [
            'date'              => ['required', 'date', 'before_or_equal:today'],
            'type'              => ['required', Rule::enum(AgentPaymentTypeEnum::class)],
            'purchase_order_id' => ['nullable', 'integer', 'required_if:type,'.$supplierPayments],
            'stock_delivery_id' => ['nullable', 'integer'],
            'reference'         => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $type = AgentPaymentTypeEnum::tryFrom((string) $this->get('type'));
        if ($type && $type->isMoneyIn() !== $this->agentPayment->type->isMoneyIn()) {
            $validator->errors()->add('type', __('Money in can not become money out, or the other way round: remove the line and record it again.'));
        }

        if ($purchaseOrderId = $this->get('purchase_order_id')) {
            $belongs = PurchaseOrder::whereKey($purchaseOrderId)->where('agent_id', $this->agentPayment->agent_id)->where('organisation_id', $this->agentPayment->organisation_id)->exists();
            if (!$belongs) {
                $validator->errors()->add('purchase_order_id', __('Choose an order this client placed through you.'));
            }
        }

        if ($stockDeliveryId = $this->get('stock_delivery_id')) {
            $belongs = StockDelivery::whereKey($stockDeliveryId)->where('agent_id', $this->agentPayment->agent_id)->where('organisation_id', $this->agentPayment->organisation_id)->exists();
            if (!$belongs) {
                $validator->errors()->add('stock_delivery_id', __('Choose a container of this client that comes through you.'));
            }
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        $agentOrganisationId = $this->agentPayment->agent?->organisation_id;

        return $agentOrganisationId !== null && $request->user()->authTo("procurement.$agentOrganisationId.edit");
    }

    public function asController(AgentPayment $agentPayment, ActionRequest $request): AgentPayment
    {
        $this->agentPayment = $agentPayment;
        $this->initialisation($agentPayment->agent->organisation, $request);

        return $this->handle($agentPayment, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
