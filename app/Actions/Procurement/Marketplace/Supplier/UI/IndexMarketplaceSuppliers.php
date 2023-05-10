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
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
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
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('suppliers.code', 'LIKE', "$value%")
                    ->orWhere('suppliers.name', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SUPPLIERS->value);

        return QueryBuilder::for(Supplier::class)
            ->defaultSort('suppliers.code')
            ->select(['code', 'slug', 'name'])
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::SUPPLIERS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::SUPPLIERS->value)
                ->pageName(TabsAbbreviationEnum::SUPPLIERS->value.'Page');
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
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
        return SupplierResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request)
    {
        $parent = $request->route()->parameters == [] ? app('currentTenant') : last($request->route()->paramenters());
        return Inertia::render(
            'Procurement/MarketplaceSuppliers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('marketplace suppliers'),
                'pageHead'    => [
                    'title'   => __('marketplace suppliers'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.marketplace-suppliers.index' ? [
                        'route' => [
                            'name'       => 'procurement.marketplace-suppliers.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('marketplace suppliers')
                    ] : false,
                ],
                'data'   => SupplierResource::collection($suppliers),


            ]
        )->table($this->tableStructure($parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('marketplace supplier'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'procurement.marketplace-suppliers.index'            =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'procurement.marketplace-suppliers.index',
                        null
                    ]
                ),
            ),


            'procurement.marketplace-suppliers.show.marketplace-suppliers.index' =>
            array_merge(
                (new ShowMarketplaceAgent())->getBreadcrumbs($routeParameters['agent']),
                $headCrumb(
                    [
                        'name'      => 'procurement.marketplace-agents.show.marketplace-suppliers.index',
                        'parameters'=>
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
