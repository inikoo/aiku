<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\HumanResources\EmployeeInertiaResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class IndexEmployee extends InertiaAction
{
    use HasUIEmployees;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('employees.name', 'LIKE', "%$value%")
                    ->orWhere('employees.slug', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Employee::class)
            ->defaultSort('employees.slug')
            ->select(['slug', 'id', 'worker_number', 'name'])
            ->with('jobPositions')
            ->allowedSorts(['slug', 'worker_number', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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


    public function jsonResponse(): AnonymousResourceCollection
    {
        return EmployeeResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $employees)
    {
        return Inertia::render(
            'HumanResources/Employees',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('employees'),
                'pageHead'    => [
                    'title' => __('employees'),
                    'create'  => $this->canEdit ? [
                        'route' => [
                            'name'       => 'hr.employees.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('employee')
                    ] : false,
                ],
                'employees'   => EmployeeInertiaResource::collection($employees),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'job_positions', label: __('position'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'actions', label: __('actions'))
                ->defaultSort('slug');
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle();
    }


}
