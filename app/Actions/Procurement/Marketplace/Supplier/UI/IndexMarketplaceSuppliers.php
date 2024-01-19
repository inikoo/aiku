<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowMarketplaceAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\MarketplaceSupplierResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\OrganisationSupplier;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexMarketplaceSuppliers extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Agent|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('suppliers.contact_name', $value)
                    ->orWhere('suppliers.slug', 'ILIKE', "$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Supplier::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('suppliers.code')
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', '=', 'suppliers.id')
            ->select(['suppliers.code', 'suppliers.slug', 'suppliers.name', 'number_supplier_products', 'suppliers.location'])
            ->addSelect([
                'adoption' => OrganisationSupplier::select('organisation_supplier.status')
                    ->whereColumn('organisation_supplier.supplier_id', 'suppliers.id')
                    ->where('organisation_supplier.organisation_id', app('currentTenant')->id)
                    ->limit(1)
            ])
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('suppliers.agent_id', $parent->id);
                } else {
                    $query->whereNull('suppliers.agent_id');
                }
            })
            ->allowedSorts(['code', 'name', 'number_supplier_products'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no suppliers'),
                        'description' => $this->canEdit ? __('Get started by creating a new supplier.') : null,
                        'count'       => app('currentTenant')->stats->number_agents,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new supplier'),
                            'label'   => __('supplier'),
                            'route'   => [
                                'name'       => 'grp.org.procurement.marketplace.suppliers.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'adoption', label: [
                    'type'    => 'icon',
                    'data'    => ['fal', 'fa-yin-yang'],
                    'tooltip' => __('adoption')
                ], canBeHidden: false)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'location', label: __('location'), canBeHidden: false)
                ->column(key: 'number_supplier_products', label: __('supplier products'), canBeHidden: false, sortable: true, searchable: true)
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

        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    public function inAgent(Agent $agent, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($agent);
    }

    public function jsonResponse(LengthAwarePaginator $suppliers): AnonymousResourceCollection
    {
        return MarketplaceSupplierResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters == []
            ?
            app('currentTenant')
            :
            $request->route()->parameters['agent'];

        $title = match (class_basename($parent)) {
            'AgentOrganisation' => __('suppliers'),
            default             => __("supplier's marketplace")
        };

        return Inertia::render(
            'Procurement/MarketplaceSuppliers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => $title,
                'pageHead'    => [
                    'title'  => $title,
                    'actions'=> [
                        $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new supplier'),
                            'label'   => __('supplier'),
                            'route'   =>
                                match (class_basename($parent)) {
                                    'AgentOrganisation' => [
                                        'name'       => 'grp.org.procurement.marketplace.agents.show.suppliers.create',
                                        'parameters' => array_values($request->route()->originalParameters())
                                    ],
                                    default => [
                                        'name'       => 'grp.org.procurement.marketplace.suppliers.create',
                                        'parameters' => array_values($request->route()->originalParameters())
                                    ],
                                },
                        ] : false,
                    ]
                ],
                'data'        => MarketplaceSupplierResource::collection($suppliers),


            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __("supplier's marketplace"),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.procurement.marketplace.suppliers.index' =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.org.procurement.marketplace.suppliers.index',
                        null
                    ]
                ),
            ),


            'grp.org.procurement.marketplace.agents.show.suppliers.index' =>
            array_merge(
                (new ShowMarketplaceAgent())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.procurement.marketplace.agents.show.suppliers.index',
                        'parameters' =>
                            [
                                $routeParameters['agent']->slug
                            ]
                    ]
                )
            ),
            default => []
        };
    }
}
