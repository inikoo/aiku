<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 19:07:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\UI;

use App\Actions\GrpAction;
use App\Actions\UI\SupplyChain\ShowSupplyChainDashboard;
use App\Http\Resources\Procurement\MarketplaceAgentResource;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\Agent;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexAgents extends GrpAction
{
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('agents.code', $value)
                    ->orWhereAnyWordStartWith('agents.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Agent::class);

        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }
        */

        /** @noinspection PhpUndefinedMethodInspection */
        return $queryBuilder
            ->leftJoin('agent_stats', 'agent_stats.agent_id', '=', 'agents.id')
            ->defaultSort('agents.code')
            ->select(['code', 'name', 'slug', 'number_suppliers', 'number_supplier_products', 'location'])
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
                        'count'       => $parent->procurementStats->number_agents,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new agent'),
                            'label'   => __('agent'),
                            'route'   => [
                                'name'       => 'grp.supply-chain.agents.create',
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
        $this->canEdit = $request->user()->hasPermissionTo('supply-chain.edit');
        return $request->user()->hasPermissionTo('supply-chain.view');
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {

        $this->initialisation(app('group'), $request);

        return $this->handle();
    }


    public function jsonResponse(LengthAwarePaginator $agent): AnonymousResourceCollection
    {
        return MarketplaceAgentResource::collection($agent);
    }


    public function htmlResponse(LengthAwarePaginator $agent, ActionRequest $request): Response
    {


        return Inertia::render(
            'SupplyChain/Agents',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __("agents"),
                'pageHead'    => [
                    'title'  => __("agents"),

                    'actions'=> [
                        $this->canEdit && $request->route()->getName() == 'grp.supply-chain.agents.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new agent'),
                            'label'   => __('agent'),
                            'route'   => [
                                'name'       => 'grp.supply-chain.agents.create',
                            ]
                        ] : false,
                    ]
                ],
                'data'        => MarketplaceAgentResource::collection($agent),
            ]
        )->table($this->tableStructure($this->group));
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowSupplyChainDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.supply-chain.agents.index'
                            ],
                            'label' => __("agent's marketplace"),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
