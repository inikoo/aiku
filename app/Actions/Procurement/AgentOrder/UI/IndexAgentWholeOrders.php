<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentOrder\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\SupplyChain\AspoDeposit\DepositRequestStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * The agent's list of the orders its clients sent to several of its suppliers at once, one row per
 * client agent order, so the agent finds the whole order to confirm and to ask its deposits for.
 */
class IndexAgentWholeOrders extends OrgAction
{
    use WithProcurementAuthorisation;
    use WithAgentOrganisation;

    public function handle(Agent $agent, ?string $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('purchase_orders.agent_order_reference', $value)
                    ->orWhereStartWith('purchase_orders.parent_code', $value)
                    ->orWhereAnyWordStartWith('organisations.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $notLive     = "'".PurchaseOrderStateEnum::CANCELLED->value."','".PurchaseOrderStateEnum::NOT_RECEIVED->value."'";
        $awaitingDeposit = "'".PurchaseOrderStateEnum::SUBMITTED->value."','".PurchaseOrderStateEnum::CONFIRMED->value."'";
        $cancelledRequest = "'".DepositRequestStateEnum::CANCELLED->value."'";

        return QueryBuilder::for(PurchaseOrder::class)
            ->join('organisations', 'purchase_orders.organisation_id', 'organisations.id')
            ->join('currencies', 'organisations.currency_id', 'currencies.id')
            ->where('purchase_orders.agent_id', $agent->id)
            ->where('purchase_orders.parent_type', 'OrgSupplier')
            ->where('purchase_orders.state', '!=', PurchaseOrderStateEnum::IN_PROCESS)
            ->whereNotNull('purchase_orders.agent_order_reference')
            ->groupBy('purchase_orders.organisation_id', 'purchase_orders.agent_order_reference', 'organisations.name', 'currencies.code')
            ->select('purchase_orders.agent_order_reference as reference')
            ->addSelect('organisations.name as organisation_name', 'currencies.code as org_currency_code')
            ->selectRaw('count(*) as number_supplier_orders')
            ->selectRaw("string_agg(purchase_orders.parent_code, ', ' order by purchase_orders.parent_code) as suppliers")
            ->selectRaw("coalesce(sum(purchase_orders.number_current_purchase_order_transactions) filter (where purchase_orders.state not in ($notLive)), 0) as number_items")
            ->selectRaw("coalesce(sum(purchase_orders.cost_items * coalesce(purchase_orders.org_exchange, 1)) filter (where purchase_orders.state not in ($notLive)), 0) as org_total_cost")
            ->selectRaw('min(purchase_orders.date) as date')
            ->selectRaw('array_to_json(array_agg(distinct purchase_orders.state)) as states')
            ->selectRaw('(array_agg(purchase_orders.slug order by purchase_orders.id))[1] as slug')
            ->selectRaw("count(*) filter (where purchase_orders.state in ($awaitingDeposit)) as number_awaiting_deposit")
            ->selectRaw("count(*) filter (where purchase_orders.state in ($awaitingDeposit) and exists (select 1 from deposit_request_items join deposit_requests on deposit_requests.id = deposit_request_items.deposit_request_id where deposit_request_items.purchase_order_id = purchase_orders.id and deposit_requests.state != $cancelledRequest)) as number_with_deposit")
            ->selectRaw('max(purchase_orders.id) as last_id')
            ->defaultSort('-last_id')
            ->allowedSorts(['reference', 'organisation_name', 'date', 'number_supplier_orders', 'org_total_cost', 'last_id'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Agent order'), __('Agent orders')])
                ->column(key: 'state', label: __('State'), canBeHidden: false)
                ->column(key: 'reference', label: __('Reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'organisation_name', label: __('Client'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'suppliers', label: __('Suppliers'), canBeHidden: false)
                ->column(key: 'number_items', label: __('Items'), canBeHidden: false, align: 'right')
                ->column(key: 'date', label: __('Date'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'org_total_cost', label: __('Amount'), canBeHidden: false, sortable: true, type: 'currency')
                ->column(key: 'deposits', label: __('Deposits'), canBeHidden: false)
                ->defaultSort('-last_id');
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);

        return $this->handle($agent);
    }

    public function htmlResponse(LengthAwarePaginator $agentOrders, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/AgentOrders',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->organisation),
                'title'       => __('Agent orders'),
                'pageHead'    => [
                    'title' => __('Agent orders'),
                    'icon'  => ['icon' => ['fal', 'fa-boxes'], 'title' => __('Agent orders')],
                ],
                'data'        => $agentOrders->through(fn ($row) => [
                    'reference'              => $row->reference,
                    'organisation_name'      => $row->organisation_name,
                    'number_supplier_orders' => (int) $row->number_supplier_orders,
                    'suppliers'              => $row->suppliers,
                    'number_items'           => (int) $row->number_items,
                    'date'                   => $row->date,
                    'org_total_cost'         => $row->org_total_cost,
                    'org_currency_code'      => $row->org_currency_code,
                    'state'                  => IndexAgentOrders::stateOf(json_decode($row->states, true) ?? []),
                    'deposits'               => [
                        'number_awaiting_deposit'         => (int) $row->number_awaiting_deposit,
                        'number_with_deposit' => (int) $row->number_with_deposit,
                    ],
                    'route'                  => [
                        'name'       => 'grp.org.agent.purchase_orders.whole_order',
                        'parameters' => [$this->organisation->slug, $row->slug],
                    ],
                ]),
            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(Organisation $organisation): array
    {
        return array_merge(
            ShowProcurementDashboard::make()->getBreadcrumbs(['organisation' => $organisation->slug]),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => __('Agent orders'),
                        'icon'  => 'fal fa-boxes',
                        'route' => [
                            'name'       => 'grp.org.agent.purchase_orders.agent_orders',
                            'parameters' => [$organisation->slug],
                        ],
                    ],
                ],
            ]
        );
    }
}
