<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 19:07:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\AgentOrganisationTabsEnum;
use App\Http\Resources\Procurement\AgentResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrgAgent;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexOrgAgents extends OrgAction
{
    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('organisations.code', $value)
                    ->orWhereAnyWordStartWith('organisations.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }



        $queryBuilder = QueryBuilder::for(OrgAgent::class);

        return $queryBuilder
            ->defaultSort('organisations.code')
            ->leftJoin('organisations', 'organisation_id', 'organisations.id')
            ->select(['organisations.code', 'organisations.name', 'organisations.slug', 'organisations.location', 'agent_stats.number_suppliers', 'agent_stats.number_purchase_orders', 'agent_stats.number_supplier_products'])
            ->leftJoin('agents', 'agents.id', 'org_agents.agent_id')
            ->leftJoin('agent_stats', 'agent_stats.agent_id', 'agents.id')
            ->where('org_agents.organisation_id', $this->organisation->id)
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null): Closure
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
                        'count'       => $this->organisation->stats->number_agents,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new agent'),
                            'label'   => __('agent'),
                            'route'   => [
                                'name' => 'grp.procurement.agents.create',

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
        $this->canEdit = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view")
            );
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request)->withTab(AgentOrganisationTabsEnum::values());

        return $this->handle();
    }


    public function jsonResponse(LengthAwarePaginator $agents): AnonymousResourceCollection
    {
        return AgentResource::collection($agents);
    }


    public function htmlResponse(LengthAwarePaginator $agents, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/OrgAgents',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('agents'),
                'pageHead'    => [
                    'title'   => __('agents'),
                    'icon'    => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-people-arrows'
                    ],
                    'actions' => [
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

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.agents.index',
                                'parameters' => [$this->organisation->slug]
                            ],
                            'label' => __('agents'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
