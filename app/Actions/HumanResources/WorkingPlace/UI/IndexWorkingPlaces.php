<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\HumanResources\WorkPlaceInertiaResource;
use App\Http\Resources\HumanResources\WorkPlaceResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Workplace;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWorkingPlaces extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('workplaces.name', 'ILIKE', "%$value%")
                    ->orWhere('workplaces.slug', 'ILIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::WORKING_PLACES->value);

        return QueryBuilder::for(Workplace::class)
            ->defaultSort('slug')
            ->select(['slug', 'id', 'name', 'type'])
            ->allowedSorts(['slug','name'])
            ->allowedFilters([$globalSearch, 'slug', 'name', 'type'])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::EMPLOYEES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::WORKING_PLACES->value)
                ->pageName(TabsAbbreviationEnum::WORKING_PLACES->value.'Page')
                ->withGlobalSearch()
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
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


    public function jsonResponse(LengthAwarePaginator $workplace): AnonymousResourceCollection
    {
        return WorkPlaceResource::collection($workplace);
    }


    public function htmlResponse(LengthAwarePaginator $workplace): Response
    {
        return Inertia::render(
            'HumanResources/WorkingPlaces',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('working places'),
                'pageHead'    => [
                    'title'  => __('working places'),
                    'create' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'hr.working-places.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('working place')
                    ] : false,
                ],
                'data'        => WorkPlaceInertiaResource::collection($workplace),
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
                            'name' => 'hr.working-places.index'
                        ],
                        'label' => __('working places'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
