<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\HumanResources\ClockingMachineInertiaResource;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Models\ClockingMachine;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexClockingMachines extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('clocking_machines.slug', 'ILIKE', "%$value%")
                    ->orWhere('clocking_machines.code', 'ILIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::CLOCKING_MACHINES->value);

        return QueryBuilder::for(ClockingMachine::class)
            ->defaultSort('clocking_machines.slug')
            ->select(['slug', 'id ', 'code'])
            ->allowedSorts(['slug', 'code'])
            ->allowedFilters([$globalSearch, 'slug', 'code'])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::CLOCKING_MACHINES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::CLOCKING_MACHINES->value)
                ->pageName(TabsAbbreviationEnum::CLOCKING_MACHINES->value.'Page')
                ->withGlobalSearch()
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $clockingMachines): AnonymousResourceCollection
    {
        return ClockingMachineResource::collection($clockingMachines);
    }


    public function htmlResponse(LengthAwarePaginator $clockingMachines): Response
    {
        return Inertia::render(
            'HumanResources/ClockingMachines',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('clocking machines'),
                'pageHead'    => [
                    'title'  => __('clocking machines'),
                    'create' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'hr.clocking-machines.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('clocking machine')
                    ] : false,
                ],
                'data'        => ClockingMachineInertiaResource::collection($clockingMachines),
            ]
        )->table($this->tableStructure());
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'hr.clocking-machines.index'
                        ],
                        'label' => __('clocking machines'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
