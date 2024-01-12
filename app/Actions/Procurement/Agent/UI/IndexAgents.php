<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:58 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\AgentTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
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

class IndexAgents extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('agents.code', 'ILIKE', "%$value%")
                    ->orWhere('agents.name', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(AgentOrganisation::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('agents.code')
            ->select(['code', 'name', 'slug', 'location', 'number_suppliers', 'number_purchase_orders', 'number_supplier_products'])
            ->leftJoin('agents', 'agents.id', 'agent_tenant.agent_id')
            ->leftJoin('agent_stats', 'agent_stats.agent_id', 'agents.id')
            ->where('agent_tenant.tenant_id', app('currentTenant')->id)
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix=null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
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
                                'name'       => 'grp.procurement.agents.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_suppliers', label: __('suppliers'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_purchase_orders', label: __('purchase orders'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_supplier_products', label: __('supplier products'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'location', label: __('location'), canBeHidden: false)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('procurement.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(AgentTabsEnum::values());
        return $this->handle();
    }


    public function jsonResponse(LengthAwarePaginator $agents): AnonymousResourceCollection
    {
        return AgentResource::collection($agents);
    }


    public function htmlResponse(LengthAwarePaginator $agents): Response
    {

        return Inertia::render(
            'Procurement/Agents',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('agents'),
                'pageHead'    => [
                    'title'   => __('agents'),
                    'icon'    => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-people-arrows'
                    ],
                    'actions'=> [
                        $this->canEdit && $request->route()->getName() == 'grp.procurement.agents.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new agent'),
                            'label'   => __('agent'),
                            'route'   => [
                                'name'       => 'grp.procurement.agents.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ]
                ],
                'data'        => AgentResource::collection($agents),
            ]
        )->table($this->tableStructure());
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
                                'name' => 'grp.procurement.agents.index'
                            ],
                            'label' => __('agents'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
