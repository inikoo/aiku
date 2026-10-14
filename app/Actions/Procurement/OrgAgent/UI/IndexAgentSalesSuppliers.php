<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Http\Resources\Procurement\AgentSalesSuppliersResource;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Every supplier of the agent with what its products sold at cost, and how many of them are out of stock, about to
 * run out or not selling in at least one organisation. Each row opens the supplier's own sales dashboard.
 */
class IndexAgentSalesSuppliers extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    public function handle(Agent $agent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $like = '%'.addcslashes((string) $value, '%_\\').'%';
            $query->whereRaw('(code collate "C" ilike ? or name ilike ?)', [$like, $like]);
        });

        return QueryBuilder::for(Supplier::withoutGlobalScopes()->fromSub(GetAgentSalesProducts::make()->suppliers($agent), 'suppliers')->selectRaw('suppliers.*, '.GetAgentSalesProducts::DERIVED))
            ->defaultSort('-sold')
            ->allowedSorts(['code', 'name', 'products', 'selling', 'sold', 'this_year', 'out_of_stock', 'low', 'dead', 'faults', ...GetAgentSalesProducts::make()->derivedSorts()])
            ->allowedFilters([$globalSearch])
            ->orderBy('id')
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('Supplier'), __('Suppliers')])
                ->withEmptyState(['title' => __('No suppliers found')])
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true)
                ->column(key: 'products', label: __('Products'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'selling', label: __('Products that sold'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'sold', label: __('Sold, 12 months, at cost'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'this_year', label: __('This year, at cost'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'last_year', label: __('Same months last year'), canBeHidden: false, align: 'right')
                ->column(key: 'change_percent', label: __('Change'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'rating', label: __('Rating'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'refund_percent', label: __('Refunds %'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'faults', label: __('Supplier faults'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'short_percent', label: __('Short deliveries %'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'out_of_stock', label: __('Out of stock'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'low', label: __('About to run out'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'dead', label: __('Not selling'), canBeHidden: false, sortable: true, align: 'right');
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): Agent
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent, 404);

        return $agent;
    }

    public function htmlResponse(Agent $agent, ActionRequest $request): Response
    {
        $parameters = $request->route()->originalParameters();

        return Inertia::render(
            'Org/Procurement/AgentSalesSuppliers',
            [
                'title'       => __('Suppliers'),
                'breadcrumbs' => ShowAgentSales::make()->getBreadcrumbs($parameters, [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'label' => __('Suppliers'),
                            'route' => ['name' => 'grp.org.agent.sales.suppliers.index', 'parameters' => $parameters],
                        ],
                    ],
                ]),
                'pageHead'    => [
                    'icon'  => ['title' => __('Suppliers'), 'icon' => 'fal fa-person-dolly'],
                    'title' => __('Suppliers'),
                    'model' => __('Sales'),
                ],
                'currency'    => GetAgentSalesProducts::make()->agentCurrency($agent)[0]->code,
                'data'        => AgentSalesSuppliersResource::collection($this->handle($agent)),
            ]
        )->table($this->tableStructure());
    }
}
