<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 09:40
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseReceipt;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\MarkPurchaseOrderStage;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Models\Procurement\AgentWarehouseReceipt;
use App\Models\Procurement\AgentWarehouseStock;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent records a batch of a purchase order line arriving at its warehouse. A line can arrive in several batches;
 * together they never go over what is still to come. The first batch of an order marks its production as started.
 */
class StoreAgentWarehouseReceipt extends OrgAction
{
    use WithProcurementEditAuthorisation;

    private PurchaseOrderTransaction $purchaseOrderTransaction;

    public function handle(PurchaseOrderTransaction $purchaseOrderTransaction, User $user, array $modelData): AgentWarehouseReceipt
    {
        $receipt = DB::transaction(function () use ($purchaseOrderTransaction, $user, $modelData) {
            $purchaseOrderTransaction = PurchaseOrderTransaction::lockForUpdate()->findOrFail($purchaseOrderTransaction->id);
            $quantity                 = (float) $modelData['quantity_received'];

            self::ensureNotOverOrdered($purchaseOrderTransaction, $quantity);

            $purchaseOrder = $purchaseOrderTransaction->purchaseOrder;

            $stock = AgentWarehouseStock::create([
                'group_id'            => $purchaseOrder->group_id,
                'agent_id'            => $purchaseOrder->agent_id,
                'organisation_id'     => $purchaseOrder->organisation_id,
                'supplier_product_id' => $purchaseOrderTransaction->supplier_product_id,
                'purchase_order_id'   => $purchaseOrder->id,
                'state'               => AgentWarehouseStockStateEnum::RECEIVED,
                'quantity'            => $quantity,
            ]);

            return AgentWarehouseReceipt::create([
                'group_id'                      => $purchaseOrder->group_id,
                'agent_id'                      => $purchaseOrder->agent_id,
                'organisation_id'               => $purchaseOrder->organisation_id,
                'purchase_order_id'             => $purchaseOrder->id,
                'purchase_order_transaction_id' => $purchaseOrderTransaction->id,
                'supplier_product_id'           => $purchaseOrderTransaction->supplier_product_id,
                'agent_warehouse_stock_id'      => $stock->id,
                'received_at'                   => $modelData['received_at'],
                'quantity_received'             => $quantity,
                'notes'                         => $modelData['notes'] ?? null,
                'received_by_id'                => $user->id,
            ]);
        });

        $purchaseOrder = $receipt->purchaseOrder;
        if ($purchaseOrder->production_started_at === null) {
            UpdatePurchaseOrder::make()->action($purchaseOrder, ['production_started_at' => Carbon::parse($modelData['received_at'])]);
        }

        return $receipt;
    }

    /**
     * What the agent kept from the batches already received for the line: received less rejected.
     */
    public static function quantityAccepted(PurchaseOrderTransaction $purchaseOrderTransaction, ?AgentWarehouseReceipt $except = null): float
    {
        return (float) AgentWarehouseReceipt::where('purchase_order_transaction_id', $purchaseOrderTransaction->id)
            ->when($except, fn ($query) => $query->where('id', '!=', $except->id))
            ->sum(DB::raw('quantity_received - coalesce(quantity_rejected, 0)'));
    }

    public static function quantityOutstanding(PurchaseOrderTransaction $purchaseOrderTransaction, ?AgentWarehouseReceipt $except = null): float
    {
        return max((float) $purchaseOrderTransaction->quantity_ordered - self::quantityAccepted($purchaseOrderTransaction, $except), 0);
    }

    /**
     * @throws ValidationException
     */
    public static function ensureNotOverOrdered(PurchaseOrderTransaction $purchaseOrderTransaction, float $quantityAccepted, ?AgentWarehouseReceipt $except = null): void
    {
        $outstanding = self::quantityOutstanding($purchaseOrderTransaction, $except);

        if ($quantityAccepted > $outstanding + 0.0001) {
            throw ValidationException::withMessages([
                'quantity_received' => __('Only :quantity units of this line are still to come', ['quantity' => trimDecimalZeros($outstanding)]),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'received_at'       => ['required', 'date', 'before_or_equal:'.Carbon::parse(MarkPurchaseOrderStage::today($this->organisation))->addDay()->toDateString()],
            'quantity_received' => ['required', 'numeric', 'gt:0'],
            'notes'             => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public static function isReceivable(PurchaseOrderTransaction $purchaseOrderTransaction): bool
    {
        $purchaseOrder = $purchaseOrderTransaction->purchaseOrder;

        return $purchaseOrder->isAgentOrder()
            && in_array($purchaseOrder->state, [PurchaseOrderStateEnum::SUBMITTED, PurchaseOrderStateEnum::CONFIRMED], true)
            && in_array($purchaseOrderTransaction->state, [PurchaseOrderTransactionStateEnum::SUBMITTED, PurchaseOrderTransactionStateEnum::CONFIRMED], true);
    }

    public function afterValidator(Validator $validator): void
    {
        if (!self::isReceivable($this->purchaseOrderTransaction)) {
            $validator->errors()->add('quantity_received', __('Only the lines of a sent order that are not cancelled can be received'));
        }
    }

    public function asController(PurchaseOrderTransaction $purchaseOrderTransaction, ActionRequest $request): AgentWarehouseReceipt
    {
        $purchaseOrder = $purchaseOrderTransaction->purchaseOrder;
        abort_unless($purchaseOrder->isAgentOrder() && $purchaseOrder->agent?->organisation, 404);

        $this->purchaseOrderTransaction = $purchaseOrderTransaction;
        $this->initialisation($purchaseOrder->agent->organisation, $request);

        return $this->handle($purchaseOrderTransaction, $request->user(), $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
