<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Picking\Picker\Json;

use App\Actions\OrgAction;
use App\Http\Resources\Ordering\PickersResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetPickers extends OrgAction
{
    public function handle(Organisation $organisation): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {

            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('employees.contact_name', $value)
                    ->orWhereStartWith('employees.alias', $value);
            });
        });



        $queryBuilder = QueryBuilder::for(Employee::class)
        ->where('employees.organisation_id', $organisation->id)
        ->leftJoin('employee_has_job_positions', 'employee_has_job_positions.employee_id', '=', 'employees.id')
        ->leftJoin('job_positions', 'employee_has_job_positions.job_position_id', '=', 'job_positions.id')
        ->where('job_positions.organisation_id', $organisation->id)
        ->where('job_positions.name', 'Picker');

        $queryBuilder
            ->defaultSort('employees.id')
            ->select([
                'employees.id',
                'employees.contact_name',
                'employees.alias',
            ]);


        return $queryBuilder->allowedSorts(['contact_name','alias'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("human-resources.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function jsonResponse(LengthAwarePaginator $pickers): AnonymousResourceCollection
    {
        return PickersResource::collection($pickers);
    }
}
