<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\DepositRequest;

use App\Actions\SupplyChain\AgentPayment\UI\IndexAgentLedger;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\SupplyChain\AspoDeposit\DepositRequestItemKindEnum;
use App\Enums\SupplyChain\AspoDeposit\DepositRequestStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * What an agent can still ask a client for: on each open supplier order the advance (the supplier's advance %,
 * 30 when not set, less what was already asked for) and the rest of the order, and whatever brings the client's
 * wallet back to zero. Amounts are in the agent's currency; orders in another currency are left out.
 */
class GetAgentMoneyRequestLines
{
    use AsObject;

    public const float DEFAULT_ADVANCE_PERCENTAGE = 30;

    /**
     * @return array{currency_code: string, wallet: array{received: float, paid: float, balance: float}, negative_balance: float, orders: list<array{purchase_order_id: int, reference: string, agent_order_reference: string|null, supplier_id: int|null, supplier_code: string|null, supplier_name: string|null, total: float, requested: float, advance_percentage: float, advance_due: float, balance_due: float}>}
     */
    public function handle(Agent $agent, Organisation $client, ?string $agentOrderReference = null): array
    {
        $currency = $agent->organisation->currency;
        $wallet   = collect(IndexAgentLedger::make()->wallets($agent))
            ->first(fn (array $wallet) => $wallet['organisation_id'] === $client->id && $wallet['currency_code'] === $currency->code)
            ?? ['received' => 0.0, 'paid' => 0.0, 'balance' => 0.0];

        $requested = DB::table('deposit_request_items')
            ->join('deposit_requests', 'deposit_requests.id', 'deposit_request_items.deposit_request_id')
            ->where('deposit_requests.state', '!=', DepositRequestStateEnum::CANCELLED->value)
            ->whereNotNull('deposit_request_items.purchase_order_id')
            ->groupBy('deposit_request_items.purchase_order_id')
            ->selectRaw('deposit_request_items.purchase_order_id, sum(deposit_request_items.amount) as amount');

        $orders = PurchaseOrder::query()
            ->where('purchase_orders.organisation_id', $client->id)
            ->where('purchase_orders.agent_id', $agent->id)
            ->where('purchase_orders.currency_id', $currency->id)
            ->whereNotNull('purchase_orders.agent_order_reference')
            ->whereIn('purchase_orders.state', [PurchaseOrderStateEnum::SUBMITTED, PurchaseOrderStateEnum::CONFIRMED])
            ->when($agentOrderReference, fn ($query) => $query->where('purchase_orders.agent_order_reference', $agentOrderReference))
            ->leftJoin('suppliers', 'suppliers.id', 'purchase_orders.supplier_id')
            ->leftJoinSub($requested, 'requested', 'requested.purchase_order_id', 'purchase_orders.id')
            ->orderBy('purchase_orders.agent_order_reference')
            ->orderBy('suppliers.code')
            ->get([
                'purchase_orders.id',
                'purchase_orders.reference',
                'purchase_orders.agent_order_reference',
                'purchase_orders.supplier_id',
                'purchase_orders.cost_total',
                'suppliers.code as supplier_code',
                'suppliers.name as supplier_name',
                'suppliers.advance_percentage',
                'requested.amount as requested',
            ])
            ->map(function (PurchaseOrder $purchaseOrder) {
                $total      = round((float) $purchaseOrder->cost_total, 2);
                $requested  = round((float) $purchaseOrder->requested, 2);
                $percentage = $purchaseOrder->advance_percentage !== null ? (float) $purchaseOrder->advance_percentage : self::DEFAULT_ADVANCE_PERCENTAGE;
                $advanceDue = max(0, round($total * $percentage / 100 - $requested, 2));

                return [
                    'purchase_order_id'     => $purchaseOrder->id,
                    'reference'             => $purchaseOrder->reference,
                    'agent_order_reference' => $purchaseOrder->agent_order_reference,
                    'supplier_id'           => $purchaseOrder->supplier_id,
                    'supplier_code'         => $purchaseOrder->supplier_code,
                    'supplier_name'         => $purchaseOrder->supplier_name,
                    'total'                 => $total,
                    'requested'             => $requested,
                    'advance_percentage'    => $percentage,
                    'advance_due'           => $advanceDue,
                    'balance_due'           => max(0, round($total - $requested - $advanceDue, 2)),
                ];
            })
            ->filter(fn (array $order) => $order['advance_due'] > 0 || $order['balance_due'] > 0)
            ->values()
            ->all();

        $walletAlreadyRequested = (float) DB::table('deposit_request_items')
            ->join('deposit_requests', 'deposit_requests.id', 'deposit_request_items.deposit_request_id')
            ->where('deposit_requests.agent_id', $agent->id)
            ->where('deposit_requests.organisation_id', $client->id)
            ->where('deposit_requests.state', DepositRequestStateEnum::REQUESTED->value)
            ->where('deposit_request_items.kind', DepositRequestItemKindEnum::WALLET->value)
            ->sum('deposit_request_items.amount');

        return [
            'currency_code'    => $currency->code,
            'wallet'           => ['received' => $wallet['received'], 'paid' => $wallet['paid'], 'balance' => $wallet['balance']],
            'negative_balance' => max(0, round(-$wallet['balance'] - $walletAlreadyRequested, 2)),
            'orders'           => $orders,
        ];
    }
}
