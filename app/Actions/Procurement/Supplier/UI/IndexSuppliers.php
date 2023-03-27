<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:14:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Closure;
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
            ->where('suppliers.type', 'supplier')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Agent') {
                    $query->where('suppliers.owner_id', $parent->id);
                }
            })
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', 'suppliers.id')
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
        $this->canEdit = $request->user()->can('procurement.suppliers.edit');
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
            'Procurement/Suppliers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('suppliers'),
                'pageHead'    => [
                    'title'   => __('suppliers'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.suppliers.index' ? [
                        'route' => [
                            'name'       => 'procurement.suppliers.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('supplier')
                    ] : false,
                ],
                'data'   => SupplierResource::collection($suppliers),


            ]
        )->table($this->tableStructure($parent));
    }
}
