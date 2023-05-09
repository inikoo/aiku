<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:58 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\AgentTenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexMarketplaceAgents extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('agents.code', 'LIKE', "$value%")
                    ->orWhere('agents.name', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::AGENTS->value);

        return QueryBuilder::for(AgentTenant::class)
            ->defaultSort('agents.code')
            ->select(['code', 'name', 'slug'])
            ->leftJoin('agents', 'agents.id', 'agent_tenant.agent_id')
            ->leftJoin('agent_stats', 'agent_stats.agent_id', 'agents.id')
            ->where('agent_tenant.tenant_id', app('currentTenant')->id)
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::AGENTS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::AGENTS->value)
                ->pageName(TabsAbbreviationEnum::AGENTS->value.'Page');
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.agents.edit');
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
        return $this->handle(app('currentTenant'));
    }


    public function jsonResponse(LengthAwarePaginator $agents): AnonymousResourceCollection
    {
        return AgentResource::collection($agents);
    }


    public function htmlResponse(LengthAwarePaginator $agents, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Procurement/Agents',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters(),
                    $parent
                ),
                'title'       => __('agents'),
                'pageHead'    => [
                    'title'   => __('agents'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.agents.index' ? [
                        'route' => [
                            'name'       => 'procurement.agents.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('agent')
                    ] : false,
                ],
                'data'      => AgentResource::collection($agents),
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
                                'name' => 'procurement.agents.index'
                            ],
                            'label' => __('agents'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
