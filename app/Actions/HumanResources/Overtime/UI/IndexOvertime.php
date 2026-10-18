<?php

namespace App\Actions\HumanResources\Overtime\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\HumanResources\Overtime\OvertimeRequestStatusEnum;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\OvertimeRequest;
use App\Models\HumanResources\OvertimeType;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOvertime extends OrgAction
{
    use WithHumanResourcesAuthorisation;
    use WithOvertimeSubNavigation;

    /**
     * @param  array{status?: string|null, from?: string|null, to?: string|null}  $modelData
     */
    public function handle(Organisation $organisation, ?string $prefix = null, array $modelData = []): LengthAwarePaginator
    {
        $status = $modelData['status'] ?? null;
        $from   = $modelData['from'] ?? null;
        $to     = $modelData['to'] ?? null;

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('staff.alias', $value)
                    ->orWhereAnyWordStartWith('approver.alias', $value)
                    ->orWhereAnyWordStartWith('overtime_types.name', $value)
                    ->orWhereAnyWordStartWith('overtime_requests.reason', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(OvertimeRequest::class)
            ->where('overtime_requests.organisation_id', $organisation->id)
            ->join('employees as staff', 'staff.id', '=', 'overtime_requests.employee_id')
            ->leftJoin('employees as approver', 'approver.id', '=', 'overtime_requests.approved_by_employee_id')
            ->leftJoin('employees as recorder', 'recorder.id', '=', 'overtime_requests.recorded_by_employee_id')
            ->join('overtime_types', 'overtime_types.id', '=', 'overtime_requests.overtime_type_id')
            ->when($status, fn ($query) => $query->where('overtime_requests.status', $status))
            ->when($from, fn ($query) => $query->whereDate('overtime_requests.requested_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('overtime_requests.requested_date', '<=', $to))
            ->select([
                'overtime_requests.id',
                'overtime_requests.employee_id',
                'overtime_requests.approved_by_employee_id',
                'overtime_requests.overtime_type_id',
                'staff.contact_name as employee_name',
                'approver.contact_name as approver_name',
                'overtime_types.name as overtime_type_name',
                'overtime_requests.requested_date',
                'overtime_requests.requested_start_at',
                'overtime_requests.requested_end_at',
                'overtime_requests.requested_duration_minutes',
                'overtime_requests.recorded_start_at',
                'overtime_requests.recorded_end_at',
                'overtime_requests.recorded_duration_minutes',
                'recorder.contact_name as recorder_name',
                'overtime_requests.lieu_requested_minutes',
                'overtime_requests.reason',
                'overtime_requests.status',
                'overtime_requests.decision_note',
                'overtime_requests.approved_at',
                'overtime_requests.rejected_at',
                'overtime_requests.source',
                'overtime_requests.id as options',
            ]);

        return $queryBuilder
            ->defaultSort('-requested_date')
            ->allowedSorts([
                'requested_date',
                'requested_start_at',
                'requested_end_at',
                'requested_duration_minutes',
                'lieu_requested_minutes',
                'status',
            ])
            ->allowedFilters([
                $globalSearch,
                'requested_date',
                'status',
            ])
            ->withPaginator($prefix, tableName: 'grp.org.hr.overtime.index')
            ->withQueryString();
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', Rule::enum(OvertimeRequestStatusEnum::class)],
            'from'   => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'to'     => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->column(
                    key: 'requested_date',
                    label: __('Requested'),
                    canBeHidden: false,
                    sortable: true
                )
                ->column(
                    key: 'employee_name',
                    label: __('Staff member'),
                    canBeHidden: false,
                    sortable: true,
                    searchable: true
                )
                ->column(
                    key: 'approver_name',
                    label: __('Approver'),
                    canBeHidden: true,
                    sortable: true,
                    searchable: true
                )
                ->column(
                    key: 'requested_start_at',
                    label: __('Request From'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'requested_duration_minutes',
                    label: __('Request duration'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'recorded_start_at',
                    label: __('Recorded from'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'recorded_end_at',
                    label: __('Recorded to'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'recorded_duration_minutes',
                    label: __('Recorded duration'),
                    canBeHidden: true,
                    sortable: true
                )
                // ->column(
                //     key: 'recorder_name',
                //     label: __('Recorded by'),
                //     canBeHidden: true,
                //     sortable: true,
                //     searchable: true
                // )
                // ->column(
                //     key: 'lieu_requested_minutes',
                //     label: __('Lieu requested'),
                //     canBeHidden: true,
                //     sortable: true
                // )
                ->column(
                    key: 'overtime_type_name',
                    label: __('Overtime type'),
                    canBeHidden: false,
                    sortable: true,
                    searchable: true
                )
                ->column(
                    key: 'status',
                    label: __('Status'),
                    canBeHidden: false,
                    sortable: true
                )
                ->column(
                    key: 'reason',
                    label: __('Reason'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'options',
                    label: __('Options'),
                    canBeHidden: false
                )
                ->defaultSort('-requested_date');
        };
    }

    public function withAvatars(LengthAwarePaginator $overtimeRequests): LengthAwarePaginator
    {
        $rows    = $overtimeRequests->getCollection();
        $avatars = Employee::avatarUrlsFor($rows->pluck('employee_id')->merge($rows->pluck('approved_by_employee_id')));

        return $overtimeRequests->through(function (OvertimeRequest $overtimeRequest) use ($avatars): OvertimeRequest {
            $overtimeRequest->setAttribute('employee_avatar', $avatars->get($overtimeRequest->employee_id));
            $overtimeRequest->setAttribute('approver_avatar', $avatars->get($overtimeRequest->approved_by_employee_id));

            return $overtimeRequest;
        });
    }

    public function htmlResponse(LengthAwarePaginator $overtimeRequests, ActionRequest $request): Response
    {
        $overtimeRequests = $this->withAvatars($overtimeRequests);

        return Inertia::render(
            'Org/HumanResources/Overtime',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Overtime'),
                'pageHead'    => [
                    'icon'          => [
                        'icon'  => ['fal', 'fa-user-hard-hat'],
                        'title' => __('Human resources')
                    ],
                    'iconRight'     => [
                        'icon'  => ['fal', 'fa-clock'],
                        'title' => __('Overtime')
                    ],
                    'title'         => __('Overtime'),
                    'actions'       => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'key'   => 'overtime request',
                            'label' => __('New overtime request'),
                            'icon'  => ['fal', 'fa-plus'],
                        ],
                    ],
                    'subNavigation' => $this->getOvertimeSubNavigation($request),
                ],
                'data'               => $overtimeRequests,
                'filters'            => [
                    'search' => (string) $request->input('filter.global', ''),
                    'status' => $this->validatedData['status'] ?? null,
                    'from'   => $this->validatedData['from'] ?? null,
                    'to'     => $this->validatedData['to'] ?? null,
                ],
                'can_edit'           => $this->canEdit,
                'employeeOptions'    => $this->organisation->employees()
                    ->orderBy('contact_name')
                    ->where('state', 'working')
                    ->get()
                    ->map(fn (Employee $employee) => [
                        'value' => $employee->id,
                        'label' => $employee->contact_name,
                    ])
                    ->values(),
                'overtimeTypeOptions' => OvertimeType::query()
                    ->where('organisation_id', $this->organisation->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(fn (OvertimeType $overtimeType) => [
                        'value' => $overtimeType->id,
                        'label' => $overtimeType->name,
                    ])
                    ->values(),
                'statusOptions'      => collect(OvertimeRequestStatusEnum::labels())
                    ->map(fn ($label, $value) => [
                        'value' => $value,
                        'label' => $label,
                    ])
                    ->values(),
            ]
        )->table($this->tableStructure());
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, null, $this->validatedData);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (string $routeName, array $routeParameters) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Overtime'),
                        'icon'  => 'fal fa-clock',
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.overtime.index' =>
            array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb($routeName, $routeParameters)
            ),
            default => [],
        };
    }
}
