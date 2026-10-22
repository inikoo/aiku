<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-22 09:12
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\OrgAction;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Opens a new container for the organisation of an agent order, as the agent does from its containers page, and puts
 * units of one of the order's items in it, for when that organisation has no container in process yet.
 */
class AddPurchaseOrderTransactionToNewAgentContainer extends OrgAction
{
    private PurchaseOrder $purchaseOrder;

    public function handle(PurchaseOrderTransaction $purchaseOrderTransaction, array $modelData): StockDelivery
    {
        $purchaseOrder = $purchaseOrderTransaction->purchaseOrder;

        return DB::transaction(function () use ($purchaseOrder, $purchaseOrderTransaction, $modelData) {
            $stockDelivery = StoreAgentStockDelivery::make()->action($purchaseOrder->agent, $purchaseOrder->orgAgentOfOrder());

            return AddPurchaseOrderItemsToStockDelivery::make()->action($stockDelivery, [
                'items' => [[
                    'purchase_order_transaction_id' => $purchaseOrderTransaction->id,
                    'unit_quantity'                 => $modelData['unit_quantity'],
                ]],
            ]);
        });
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user()->authTo("procurement.{$this->purchaseOrder->organisation_id}.edit")) {
            return true;
        }

        $agentOrganisationId = $this->purchaseOrder->isAgentOrder() ? $this->purchaseOrder->agent?->organisation_id : null;

        return $agentOrganisationId !== null && $request->user()->authTo("procurement.$agentOrganisationId.edit");
    }

    public function rules(): array
    {
        return [
            'unit_quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $orgAgent = $this->purchaseOrder->isAgentOrder() ? $this->purchaseOrder->orgAgentOfOrder() : null;

        if (!$orgAgent || $this->purchaseOrder->state !== PurchaseOrderStateEnum::CONFIRMED) {
            $validator->errors()->add('unit_quantity', __('Only the items of a confirmed agent order go in a container'));
        } elseif ($orgAgent->agent->organisation->currency_id !== $this->purchaseOrder->currency_id) {
            $validator->errors()->add('unit_quantity', __('A new container is in the agent\'s currency, and this order is in another one'));
        }
    }

    public function asController(PurchaseOrder $purchaseOrder, PurchaseOrderTransaction $purchaseOrderTransaction, ActionRequest $request): StockDelivery
    {
        abort_unless($purchaseOrderTransaction->purchase_order_id === $purchaseOrder->id, 404);
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrderTransaction, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
