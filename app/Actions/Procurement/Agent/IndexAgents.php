<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 12:55:50 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\InertiaAction;
use App\Actions\Procurement\ShowProcurementDashboard;
use App\Http\Resources\Procurement\AgentResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexAgents extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('suppliers.code', 'LIKE', "$value%")
                    ->orWhere('suppliers.name', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Agent::class)
            ->defaultSort('suppliers.code')
            ->select(['code', 'suppliers.id as id', 'name'])
            ->where('suppliers.type', 'agent')
            ->leftJoin('supplier_stats', 'supplier_stats.supplier_id', 'suppliers.id')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $request->validate();

        return $this->handle();
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return SupplierResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $agents)
    {
        return Inertia::render(
            '/Procurement/Agents',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('agents'),
                'pageHead'    => [
                    'title' => __('agents'),
                ],
                'agents'      => AgentResource::collection($agents),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        });
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowProcurementDashboard())->getBreadcrumbs(),
            [
                'procurement.agents.index' => [
                    'route' => 'procurement.agents.index',
                    'modelLabel' => [
                        'label' => __('agents')
                    ],
                ],
            ]
        );
    }

}
