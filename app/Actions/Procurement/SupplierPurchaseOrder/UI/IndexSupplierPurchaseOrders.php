<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\InertiaTable\InertiaTable;
use App\Models\Procurement\PurchaseOrder;
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
use App\Services\QueryBuilder;

class IndexSupplierPurchaseOrders extends InertiaAction
{
    public function handle(Organisation|Agent|Supplier $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('purchase_orders.number', 'ILIKE', "$value%")
                    ->orWhere('purchase_orders.status', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }
        ;
        return QueryBuilder::for(PurchaseOrder::class)
            ->defaultSort('purchase_orders.number')
            ->select(['number', 'slug', 'purchase_orders.status as status'])
            ->leftJoin('purchase_order_items', 'purchase_order_items.purchase_order_id', 'purchase_orders.id')

            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('purchase_orders.provider_id', $parent->id);
                } elseif (class_basename($parent) == 'Organisation') {
                    $query->where('purchase_orders.provider_id', $parent->id);
                } elseif (class_basename($parent) == 'Supplier') {
                    $query->where('purchase_orders.provider_id', $parent->id);
                }
            })
            ->allowedSorts(['number', 'slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
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
        return PurchaseOrderResource::collection($suppliers);
    }
    public function inAgent(Agent $agent): LengthAwarePaginator
    {
        $this->validateAttributes();
        return $this->handle($agent);
    }

    public function htmlResponse(LengthAwarePaginator $purchase_orders, ActionRequest $request): Response
    {
        $parent = $request->route()->originalParameters() == [] ? app('currentTenant') : last($request->route()->paramenters());
        return Inertia::render(
            'Procurement/SupplierPurchaseOrders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('supplier purchase orders'),
                'pageHead'    => [
                    'title'   => __('supplier purchase orders'),
                    'create'  => $this->canEdit && $request->route()->getName()=='grp.org.procurement.supplier-purchase-orders.index' ? [
                        'route' => [
                            'name'       => 'grp.org.procurement.supplier-purchase-orders.create',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label'=> __('supplier deliveries')
                    ] : false,
                ],
                'data'   => PurchaseOrderResource::collection($purchase_orders),


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
                                'name' => 'grp.org.procurement.supplier-purchase-orders.index'
                            ],
                            'label' => __('supplier purchase order'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
