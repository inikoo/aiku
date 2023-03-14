<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:58 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Agent\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
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
    use HasUIAgents;
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('agents.code', 'LIKE', "$value%")
                    ->orWhere('agents.name', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Agent::class)
            ->defaultSort('agents.code')
            ->select(['code', 'name', 'slug'])
            ->leftJoin('agent_stats', 'agent_stats.agent_id', 'agents.id')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.agents.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('procurement.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        //$request->validate();
        $this->initialisation($request);
        return $this->handle();
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return SupplierResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $agents)
    {
        return Inertia::render(
            'Procurement/Agents',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('agents'),
                'pageHead'    => [
                    'title' => __('agents'),
                    'create'  => $this->canEdit && $this->routeName=='procurement.agents.index' ? [
                        'route' => [
                            'name'       => 'procurement.agents.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('agent')
                    ] : false,
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



}
