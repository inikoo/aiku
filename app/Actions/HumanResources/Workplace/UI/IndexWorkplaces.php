<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace\UI;

use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\WorkplaceInertiaResource;
use App\Http\Resources\HumanResources\WorkplaceResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexWorkplaces extends OrgAction
{
    private array $originalParameters;


    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('workplaces.name', $value)
                    ->orWhereStartWith('workplaces.slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Workplace::class);

        $queryBuilder->where('organisation_id', $this->organisation->id);


        return $queryBuilder
            ->defaultSort('slug')
            ->select([
                'slug',
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
                'timezone_id',
                'address_id',
                'address_id',
            ])
            ->with('address')
            ->with('stats')
            ->allowedSorts(['slug','name'])
            ->allowedFilters([$globalSearch, 'slug', 'name', 'type'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no working places'),
                        'description' => $this->canEdit ? __('Get started by creating a new working place.') : null,
                        'count'       => 0,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new working place'),
                            'label'   => __('working place'),
                            'route'   => [
                                'name'       => 'grp.org.hr.workplaces.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $workplace): AnonymousResourceCollection
    {
        return WorkplaceResource::collection($workplace);
    }


    public function htmlResponse(LengthAwarePaginator $workplace, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/HumanResources/Workplaces',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('working places'),
                'pageHead'    => [
                    'title'  => __('working places'),
                    'actions'=> [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('working place'),
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false
                    ]
                ],

                'data'        => WorkplaceInertiaResource::collection($workplace),
            ]
        )->table($this->tableStructure());
    }


    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->originalParameters = $request->route()->originalParameters();
        return $this->handle();
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.workplaces.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('working places'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
