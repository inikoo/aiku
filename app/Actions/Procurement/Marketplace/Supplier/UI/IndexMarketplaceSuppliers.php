<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Marketplace\Agent\UI\ShowMarketplaceAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\MarketplaceSupplierResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierTenant;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexMarketplaceSuppliers extends InertiaAction
{
    public function handle(Agent|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('suppliers.code', 'ILIKE', "%$value%")
                    ->orWhere('suppliers.name', 'ILIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SUPPLIERS->value);

        return QueryBuilder::for(Supplier::class)
            ->defaultSort('suppliers.code')
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', '=', 'suppliers.id')
            ->select(['suppliers.code', 'suppliers.slug', 'suppliers.name', 'number_supplier_products', 'suppliers.location'])
            ->addSelect([
                'adoption' => SupplierTenant::select('supplier_tenant.status')
                    ->whereColumn('supplier_tenant.supplier_id', 'suppliers.id')
                    ->where('supplier_tenant.tenant_id', app('currentTenant')->id)
                    ->limit(1)
            ])
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('suppliers.owner_type', 'Agent');
                    $query->where('suppliers.owner_id', $parent->id);
                    $query->leftJoin('agents', 'suppliers.owner_id', 'agents.id');
                    $query->addSelect('agents.slug as agent_slug');
                } else {
                    $query->where('suppliers.type', 'supplier');
                }
            })
            ->allowedSorts(['code', 'name', 'number_supplier_products'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::SUPPLIERS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure(array $modelOperations = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations) {
            $table
                ->name(TabsAbbreviationEnum::SUPPLIERS->value)
                ->pageName(TabsAbbreviationEnum::SUPPLIERS->value.'Page')
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'adoption', label: 'z', canBeHidden: false)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'location', label: __('location'), canBeHidden: false)
                ->column(key: 'number_supplier_products', label: __('supplier products'), canBeHidden: false, sortable: true, searchable: true)
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


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request)
    {
        $parent = $request->route()->parameters == []
            ?
            app('currentTenant')
            :
            $request->route()->parameters['agent'];

        $title = match (class_basename($parent)) {
            'Agent' => __('suppliers'),
            default => __("supplier's marketplace")
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
                    'create' => $this->canEdit ? [
                        'route' =>

                            match (class_basename($parent)) {
                                'Agent' => [
                                    'name'       => 'procurement.marketplace.agents.show.suppliers.create',
                                    'parameters' => array_values($this->originalParameters)
                                ],
                                default => [
                                    'name'       => 'procurement.marketplace.suppliers.create',
                                    'parameters' => array_values($this->originalParameters)
                                ],
                            },


                        'label' => __('supplier')
                    ] : false,
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
            'procurement.marketplace.suppliers.index' =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'procurement.marketplace.suppliers.index',
                        null
                    ]
                ),
            ),


            'procurement.marketplace.agents.show.suppliers.index' =>
            array_merge(
                (new ShowMarketplaceAgent())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'procurement.marketplace.agents.show.suppliers.index',
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
