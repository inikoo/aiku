<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierDelivery\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\SupplierDeliveryResource;
use App\Models\Procurement\SupplierDelivery;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSupplierDeliveries extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('supplier_deliveries.number', 'LIKE', "$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SUPPLIER_DELIVERIES->value);

        return QueryBuilder::for(SupplierDelivery::class)
            ->defaultSort('supplier_deliveries.number')
            ->select(['slug', 'number'])
            ->leftJoin('supplier_deliveries', 'supplier_deliveries.id')
            ->allowedSorts(['number'])
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
                ->name(TabsAbbreviationEnum::SUPPLIER_DELIVERIES->value)
                ->pageName(TabsAbbreviationEnum::SUPPLIER_DELIVERIES->value.'Page');
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



    public function jsonResponse(LengthAwarePaginator $suppliers): AnonymousResourceCollection
    {
        return SupplierDeliveryResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request)
    {
        $parent = $request->route()->parameters == [] ? app('currentTenant') : last($request->route()->paramenters());
        return Inertia::render(
            'Procurement/SupplierDeliveries',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('supplier deliveries'),
                'pageHead'    => [
                    'title'   => __('supplier deliveries'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.supplier-deliveries.index' ? [
                        'route' => [
                            'name'       => 'procurement.supplier-deliveries.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('supplier deliveries')
                    ] : false,
                ],
                'data'   => SupplierDeliveryResource::collection($suppliers),


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
                        'label' => __('suppliers'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'procurement.suppliers.index'            =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'procurement.suppliers.index',
                        null
                    ]
                ),
            ),


            'procurement.agents.show.suppliers.index' =>
            array_merge(
                (new ShowAgent())->getBreadcrumbs($routeParameters['agent']),
                $headCrumb(
                    [
                        'name'      => 'procurement.agents.show.suppliers.index',
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
