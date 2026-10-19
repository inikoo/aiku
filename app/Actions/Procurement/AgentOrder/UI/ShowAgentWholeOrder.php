<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentOrder\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\SupplyChain\AgentPayment\AgentPaymentTypeEnum;
use App\Enums\SupplyChain\AspoDeposit\DepositRequestStateEnum;
use App\Http\Resources\Procurement\PurchaseOrdersResource;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent's view of an order a client sent to several of its suppliers at once: the agent confirms the
 * supplier orders together and asks the client for the deposits, one amount per supplier.
 */
class ShowAgentWholeOrder extends OrgAction
{
    use WithProcurementAuthorisation;
    use WithAgentOrganisation;

    private PurchaseOrder $purchaseOrder;

    private bool $agentCanAct = false;

    /**
     * @return Collection<int, PurchaseOrder>
     */
    public function handle(PurchaseOrder $purchaseOrder): Collection
    {
        $supplierPaymentTypes = array_values(array_map(fn (AgentPaymentTypeEnum $type) => $type->value, array_filter(AgentPaymentTypeEnum::cases(), fn (AgentPaymentTypeEnum $type) => $type->isSupplierPayment())));

        return $purchaseOrder->wholeAgentOrder()
            ->select('purchase_orders.*')
            ->selectSub(
                DB::table('deposit_request_items')
                    ->join('deposit_requests', 'deposit_requests.id', 'deposit_request_items.deposit_request_id')
                    ->whereColumn('deposit_request_items.purchase_order_id', 'purchase_orders.id')
                    ->where('deposit_requests.state', '!=', DepositRequestStateEnum::CANCELLED->value)
                    ->selectRaw('coalesce(sum(deposit_request_items.amount), 0)'),
                'requested_amount'
            )
            ->selectSub(
                DB::table('agent_payments')
                    ->whereColumn('agent_payments.purchase_order_id', 'purchase_orders.id')
                    ->whereIn('agent_payments.type', $supplierPaymentTypes)
                    ->whereNull('agent_payments.deleted_at')
                    ->selectRaw('coalesce(sum(agent_payments.amount), 0)'),
                'paid_to_suppliers_amount'
            )
            ->with('currency')
            ->orderBy('parent_code')
            ->get();
    }

    public function asController(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): Collection
    {
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($purchaseOrder);
        abort_unless($purchaseOrder->isAgentOrder() && $purchaseOrder->agent_order_reference, 404);

        $this->purchaseOrder = $purchaseOrder;
        $this->agentCanAct   = $this->agentEditsOwnOrder($purchaseOrder, $request->user());

        return $this->handle($purchaseOrder);
    }

    public function htmlResponse(Collection $purchaseOrders, ActionRequest $request): Response
    {
        $reference      = $this->purchaseOrder->agent_order_reference;
        $live           = $purchaseOrders->reject(fn (PurchaseOrder $purchaseOrder) => in_array($purchaseOrder->state, [PurchaseOrderStateEnum::CANCELLED, PurchaseOrderStateEnum::NOT_RECEIVED, PurchaseOrderStateEnum::IN_PROCESS], true));
        $toConfirm      = $purchaseOrders->filter(fn (PurchaseOrder $purchaseOrder) => $purchaseOrder->state === PurchaseOrderStateEnum::SUBMITTED);
        $state          = IndexAgentOrders::stateOf($purchaseOrders->map(fn (PurchaseOrder $purchaseOrder) => $purchaseOrder->state->value)->unique()->values()->all());
        $routeParameters = $request->route()->originalParameters();

        return Inertia::render(
            'Procurement/AgentWholeOrder',
            [
                'title'       => __('Agent order').' '.$reference,
                'breadcrumbs' => $this->getBreadcrumbs($routeParameters, $reference),
                'pageHead'    => [
                    'model'      => __('Agent order'),
                    'title'      => $reference,
                    'icon'       => ['icon' => ['fal', 'fa-boxes'], 'title' => __('Agent order')],
                    'afterTitle' => ['label' => $state['label']],
                ],
                'whole_order' => [
                    'reference'              => $reference,
                    'client'                 => $this->purchaseOrder->organisation->name,
                    'state'                  => $state,
                    'number_supplier_orders' => $purchaseOrders->count(),
                    'number_to_confirm'      => $toConfirm->count(),
                    'totals'                 => $this->sumByCurrency($live, fn (PurchaseOrder $purchaseOrder) => (float) $purchaseOrder->cost_total),
                    'requested'              => $this->sumByCurrency($live, fn (PurchaseOrder $purchaseOrder) => (float) $purchaseOrder->requested_amount),
                    'paid_to_suppliers'      => $this->sumByCurrency($live, fn (PurchaseOrder $purchaseOrder) => (float) $purchaseOrder->paid_to_suppliers_amount),
                    'to_pay'                 => $this->sumByCurrency($live, fn (PurchaseOrder $purchaseOrder) => max(0, (float) $purchaseOrder->cost_total - (float) $purchaseOrder->paid_to_suppliers_amount)),
                    'pdf_route'              => [
                        'name'       => 'grp.org.agent.purchase_orders.whole_order.pdf',
                        'parameters' => $routeParameters,
                    ],
                    'confirm_route'          => $this->agentCanAct && $toConfirm->isNotEmpty() ? [
                        'name'       => 'grp.models.purchase-order.agent_order.confirm',
                        'parameters' => ['purchaseOrder' => $this->purchaseOrder->id],
                        'method'     => 'patch',
                    ] : null,
                    'money_request_route'    => $this->agentCanAct && $live->isNotEmpty() ? [
                        'name'       => 'grp.org.agent.accounting.deposit_requests.create',
                        'parameters' => ['organisation' => $this->organisation->slug, 'client' => $this->purchaseOrder->organisation->slug, 'agent_order' => $reference],
                    ] : null,
                    'deposit_requests_route' => [
                        'name'       => 'grp.org.agent.accounting.deposit_requests.index',
                        'parameters' => [$this->organisation->slug],
                    ],
                ],
                'data'            => PurchaseOrdersResource::collection(
                    IndexPurchaseOrders::make()->inWholeAgentOrderOf($this->purchaseOrder)->handle($this->organisation)
                ),
            ]
        )->table(IndexPurchaseOrders::make()->inWholeAgentOrderOf($this->purchaseOrder)->tableStructure($this->organisation));
    }

    /**
     * @return array<int, array{currency_code: string, amount: float}>
     */
    private function sumByCurrency(Collection $purchaseOrders, callable $amount): array
    {
        return $purchaseOrders->groupBy(fn (PurchaseOrder $purchaseOrder) => $purchaseOrder->currency->code)
            ->map(fn ($orders, $currency) => ['currency_code' => $currency, 'amount' => (float) $orders->sum($amount)])
            ->values()->all();
    }

    public function getBreadcrumbs(array $routeParameters, string $reference): array
    {
        return array_merge(
            IndexAgentWholeOrders::make()->getBreadcrumbs($this->organisation),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => $reference,
                        'route' => [
                            'name'       => 'grp.org.agent.purchase_orders.whole_order',
                            'parameters' => $routeParameters,
                        ],
                    ],
                ],
            ]
        );
    }
}
