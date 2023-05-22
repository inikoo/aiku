<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Supplier;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSupplierPurchaseOrders extends InertiaAction
{
    public function handle(Tenant|Agent|Supplier $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('purchase_orders.number', 'LIKE', "$value%")
                    ->orWhere('purchase_orders.status', 'LIKE', "%$value%");
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::PURCHASE_ORDERS->value);
        ;
        return QueryBuilder::for(PurchaseOrder::class)
            ->defaultSort('purchase_orders.number')
            ->select(['number', 'slug', 'status'])
            ->leftJoin('purchase_order_items', 'purchase_order_items.purchase_order_id', 'purchase_orders.id')

            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('purchase_orders.provider_id', $parent->id);
                } elseif (class_basename($parent) == 'Tenant') {

                    $query->where('supplier_product_tenant.tenant_id', $parent->id);
                } elseif (class_basename($parent) == 'Supplier') {
                    $query->where('supplier_products.supplier_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::SUPPLIER_PRODUCTS->value.'Page'
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
        return PurchaseOrderResource::collection($suppliers);
    }


    public function htmlResponse(LengthAwarePaginator $suppliers, ActionRequest $request)
    {
        $parent = $request->route()->parameters == [] ? app('currentTenant') : last($request->route()->paramenters());
        return Inertia::render(
            'Procurement/SupplierPurchaseOrders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('supplier purchase orders'),
                'pageHead'    => [
                    'title'   => __('supplier purchase orders'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.supplier-purchase-orders.index' ? [
                        'route' => [
                            'name'       => 'procurement.supplier-purchase-orders.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('supplier deliveries')
                    ] : false,
                ],
                'data'   => PurchaseOrderResource::collection($suppliers),


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
                                'name' => 'procurement.supplier-purchase-orders.index'
                            ],
                            'label' => __('supplier purchase order'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
