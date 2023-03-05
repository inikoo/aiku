<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 17:45:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\HumanResources\ShowHumanResourcesDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class IndexJobPosition
{
    use AsAction;
    use WithInertia;


    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('employees.slug', 'LIKE', "%$value%")
                    ->orWhere('employees.name', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Employee::class)
            ->defaultSort('job_positions.slug')
            ->select(['slug', 'id', 'name'])
            ->allowedSorts(['slug', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return JobPositionResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $jobPositions)
    {
        return Inertia::render(
            'HumanResources/JobPositions',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('job positions'),
                'pageHead'    => [
                    'title' => __('positions'),
                ],
                'jobPositions'   => JobPositionResource::collection($jobPositions),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'job_positions', label: __('position'), canBeHidden: false, sortable: true, searchable: true)

                ->defaultSort('code');
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs(),
            [
                'hr.employees.index' => [
                    'route'      => 'hr.employees.index',
                    'modelLabel' => [
                        'label' => __('employees')
                    ],
                ],
            ]
        );
    }
}
