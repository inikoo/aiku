<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:58 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\MarketplaceAgentResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\AgentOrganisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexMarketplaceAgents extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('agents.code', 'ILIKE', "%$value%")
                    ->orWhere('agents.name', 'LIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Agent::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->leftJoin('agent_stats', 'agent_stats.agent_id', '=', 'agents.id')
            ->defaultSort('agents.code')
            ->select(['code', 'name', 'slug', 'number_suppliers', 'number_supplier_products', 'location'])
            ->addSelect([
                'adoption' => AgentOrganisation::select('agent_tenant.status')
                    ->whereColumn('agent_tenant.agent_id', 'agents.id')
                    ->where('agent_tenant.tenant_id', app('currentTenant')->id)
                    ->limit(1)
            ])
            ->allowedFilters([$globalSearch])
            ->allowedSorts(['code', 'name', 'number_suppliers', 'number_supplier_products'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {

            if($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no agents'),
                        'description' => $this->canEdit ? __('Get started by creating a new agent.') : null,
                        'count'       => app('currentTenant')->stats->number_agents,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new agent'),
                            'label'   => __('agent'),
                            'route'   => [
                                'name'       => 'procurement.marketplace.agents.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'adoption', label: [
                    'type'   => 'icon',
                    'data'   => ['fal','fa-yin-yang'],
                    'tooltip'=> __('adoption')
                ], canBeHidden: false)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'location', label: __('location'), canBeHidden: false)
                ->column(key: 'number_suppliers', label: __('suppliers'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_supplier_products', label: __('products'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);

        return $this->handle();
    }


    public function jsonResponse(LengthAwarePaginator $agent): AnonymousResourceCollection
    {
        return MarketplaceAgentResource::collection($agent);
    }


    public function htmlResponse(LengthAwarePaginator $agent, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Procurement/MarketplaceAgents',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __("agent's marketplace"),
                'pageHead'    => [
                    'title'  => __("agent's marketplace"),

                    'actions'=> [
                        $this->canEdit && $this->routeName == 'procurement.marketplace.agents.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new agent'),
                            'label'   => __('agent'),
                            'route'   => [
                                'name'       => 'procurement.marketplace.agents.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                    ]
                ],
                'data'        => MarketplaceAgentResource::collection($agent),
            ]
        )->table($this->tableStructure($parent));
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'procurement.marketplace.agents.index'
                            ],
                            'label' => __("agent's marketplace"),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
