<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 09 May 2023 09:25:51 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexPurchaseOrders extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('purchase_orders.number', 'LIKE', "$value%")
                    ->orWhere('purchase_orders.state', 'LIKE', "%$value%")
                    ->orWhere('purchase_orders.status', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::PURCHASE_ORDERS->value);

        return QueryBuilder::for(PurchaseOrder::class)
            ->defaultSort('purchase_orders.number')
            ->select(['number', 'state', 'slug', 'status'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::PURCHASE_ORDERS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::PURCHASE_ORDERS->value)
                ->pageName(TabsAbbreviationEnum::PURCHASE_ORDERS->value.'Page');
            $table
                ->withGlobalSearch()
                ->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'status', label: __('status'), canBeHidden: false, sortable: true, searchable: true)
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

//
    public function jsonResponse(LengthAwarePaginator $purchaseOrders): AnonymousResourceCollection
    {
        return PurchaseOrderResource::collection($purchaseOrders);
    }


    public function htmlResponse(LengthAwarePaginator $purchaseOrders, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Procurement/PurchaseOrders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters(),
                    $parent
                ),
                'title'       => __('purchase orders'),
                'pageHead'    => [
                    'title'   => __('purchase orders'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.purchase-orders.index' ? [
                        'route' => [
                            'name'       => 'procurement.purchase-orders.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('purchase orders')
                    ] : false,
                ],
                'data'      => PurchaseOrderResource::collection($purchaseOrders),
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
                                'name' => 'procurement.purchase-orders.index'
                            ],
                            'label' => __('purchase orders'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
