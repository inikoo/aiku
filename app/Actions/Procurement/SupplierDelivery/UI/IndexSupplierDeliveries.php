<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierDelivery\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierDeliveryResource;
use App\Models\Procurement\SupplierDelivery;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexSupplierDeliveries extends InertiaAction
{
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('supplier_deliveries.number', 'ILIKE', "$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(SupplierDelivery::class)
            ->defaultSort('supplier_deliveries.number')
            ->select(['slug', 'number'])
            ->allowedSorts(['number'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(array $modelOperations = null, $prefix=null): Closure
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
                ->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('number');
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


    public function jsonResponse(LengthAwarePaginator $suppliers): AnonymousResourceCollection
    {
        return SupplierDeliveryResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/SupplierDeliveries',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('supplier deliveries'),
                'pageHead'    => [
                    'title'  => __('supplier deliveries'),
                    'create' => $this->canEdit && $request->route()->getName() == 'grp.org.procurement.supplier-deliveries.index' ? [
                        'route' => [
                            'name'       => 'grp.org.procurement.supplier-deliveries.create',
                            'parameters' => array_values($request->route()->originalParameters())
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
                                'name' => 'grp.org.procurement.supplier-deliveries.index'
                            ],
                            'label' => __('supplier deliveries'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
