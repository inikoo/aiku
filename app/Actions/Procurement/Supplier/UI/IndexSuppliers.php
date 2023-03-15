<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexSuppliers extends InertiaAction
{
    use HasUISuppliers;

    private Agent|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('suppliers.code', 'LIKE', "$value%")
                    ->orWhere('suppliers.name', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Supplier::class)
            ->defaultSort('suppliers.code')
            ->select(['code', 'slug', 'name'])
            ->where('suppliers.type', 'supplier')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Agent') {
                    $query->where('suppliers.owner_id', $this->parent->id);
                }
            })
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', 'suppliers.id')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.suppliers.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        //$request->validate();
        $this->parent    = app('currentTenant');
        $this->initialisation($request);
        return $this->handle();
    }

    public function InAgent(Agent $agent): LengthAwarePaginator
    {
        $this->parent = $agent;
        $this->validateAttributes();
        return $this->handle();
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return SupplierResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $suppliers)
    {
        return Inertia::render(
            'Procurement/Suppliers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('suppliers'),
                'pageHead'    => [
                    'title' => __('suppliers'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.suppliers.index' ? [
                        'route' => [
                            'name'       => 'procurement.suppliers.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('supplier')
                    ] : false,
                ],
                'suppliers'   => SupplierResource::collection($suppliers),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        });
    }

}
