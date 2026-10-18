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
use App\Enums\SupplyChain\AspoDeposit\AspoDepositStateEnum;
use App\Http\Resources\Procurement\PurchaseOrdersResource;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\AspoDeposit;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Collection;
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
        return $purchaseOrder->wholeAgentOrder()
            ->with(['currency', 'deposits.depositRequestItems'])
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
                    'deposits'               => $this->sumByCurrency($live, fn (PurchaseOrder $purchaseOrder) => $this->depositsOf($purchaseOrder)),
                    'requested'              => $this->sumByCurrency($live, fn (PurchaseOrder $purchaseOrder) => $this->requestedOf($purchaseOrder)),
                    'confirm_route'          => $this->agentCanAct && $toConfirm->isNotEmpty() ? [
                        'name'       => 'grp.models.purchase-order.agent_order.confirm',
                        'parameters' => ['purchaseOrder' => $this->purchaseOrder->id],
                        'method'     => 'patch',
                    ] : null,
                    'deposits_route'         => $this->agentCanAct && $live->isNotEmpty() ? [
                        'name'       => 'grp.models.purchase-order.agent_order.deposits.request',
                        'parameters' => ['purchaseOrder' => $this->purchaseOrder->id],
                        'method'     => 'post',
                    ] : null,
                    'deposit_requests_route' => [
                        'name'       => 'grp.org.agent.accounting.deposit_requests.index',
                        'parameters' => [$this->organisation->slug],
                    ],
                ],
                'supplier_orders' => $live->map(fn (PurchaseOrder $purchaseOrder) => [
                    'id'            => $purchaseOrder->id,
                    'reference'     => $purchaseOrder->reference,
                    'supplier_code' => $purchaseOrder->parent_code,
                    'supplier_name' => $purchaseOrder->parent_name,
                    'state_label'   => $purchaseOrder->state->labels()[$purchaseOrder->state->value],
                    'currency_code' => $purchaseOrder->currency->code,
                    'cost_total'    => (float) $purchaseOrder->cost_total,
                    'deposits'      => $this->depositsOf($purchaseOrder),
                    'requested'     => $this->requestedOf($purchaseOrder),
                ])->values()->all(),
                'data'            => PurchaseOrdersResource::collection(
                    IndexPurchaseOrders::make()->inWholeAgentOrderOf($this->purchaseOrder)->handle($this->organisation)
                ),
            ]
        )->table(IndexPurchaseOrders::make()->inWholeAgentOrderOf($this->purchaseOrder)->tableStructure($this->organisation));
    }

    private function depositsOf(PurchaseOrder $purchaseOrder): float
    {
        return (float) $purchaseOrder->deposits
            ->reject(fn (AspoDeposit $deposit) => in_array($deposit->state, [AspoDepositStateEnum::CANCELLED, AspoDepositStateEnum::REFUNDED], true))
            ->sum('amount');
    }

    private function requestedOf(PurchaseOrder $purchaseOrder): float
    {
        return (float) $purchaseOrder->deposits->sum(fn (AspoDeposit $deposit) => $deposit->depositRequestItems->sum('amount'));
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
