<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierDelivery\UI;

use App\Actions\InertiaAction;
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
                $query->where('supplier_deliveries.number', 'ILIKE', "$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SUPPLIER_DELIVERIES->value);

        return QueryBuilder::for(SupplierDelivery::class)
            ->defaultSort('supplier_deliveries.number')
            ->select(['slug', 'number'])
            ->allowedSorts(['number'])
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
                ->name(TabsAbbreviationEnum::SUPPLIER_DELIVERIES->value)
                ->pageName(TabsAbbreviationEnum::SUPPLIER_DELIVERIES->value.'Page')
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('number');
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
                    'title'  => __('supplier deliveries'),
                    'create' => $this->canEdit && $this->routeName == 'procurement.supplier-deliveries.index' ? [
                        'route' => [
                            'name'       => 'procurement.supplier-deliveries.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('supplier deliveries')
                    ] : false,
                ],
                'data'        => SupplierDeliveryResource::collection($suppliers),


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
                                'name' => 'procurement.supplier-deliveries.index'
                            ],
                            'label' => __('supplier deliveries'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
