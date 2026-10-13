<?php

namespace App\Actions\HumanResources\Leave\UI;

use App\Actions\HumanResources\Leave\UpdateLeaveCover;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\Employee;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\HumanResources\Leave\LeaveStatusEnum;
use App\Http\Resources\HumanResources\LeaveResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Holiday;
use App\Models\HumanResources\JobPosition;
use App\Models\HumanResources\Leave;
use App\Models\HumanResources\LeaveApprover;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\HumanResources\LeaveTypeResolver;

class IndexLeavesAdmin extends OrgAction
{
    use WithLeaveSubNavigation;
    use WithHumanResourcesAuthorisation {
        authorize as authorizeHumanResources;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isActiveLeaveApprover($request)) {
            return true;
        }

        return $this->authorizeHumanResources($request);
    }

    private function isActiveLeaveApprover(ActionRequest $request): bool
    {
        return LeaveApprover::query()
            ->where('organisation_id', $this->organisation->id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @param  array{status?: string|null, from?: string|null, to?: string|null, type?: string|null}  $modelData
     */
    public function handle(Organisation $organisation, ?string $prefix = null, array $modelData = []): LengthAwarePaginator
    {
        $status = $modelData['status'] ?? null;
        $from   = $modelData['from'] ?? null;
        $to     = $modelData['to'] ?? null;
        $type   = $modelData['type'] ?? null;

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $term = '%'.addcslashes((string) $value, '%_\\').'%';

            $query->where(function ($query) use ($term) {
                $query->where('leaves.employee_name', 'ilike', $term)
                    ->orWhere('leaves.reason', 'ilike', $term)
                    ->orWhereHas('leaveType', fn ($query) => $query->where('name', 'ilike', $term));
            });
        });

        return QueryBuilder::for(Leave::class)
            ->where('leaves.organisation_id', $organisation->id)
            ->when($status, fn ($query) => $query->where('leaves.status', $status))
            ->when($type, fn ($query) => $query->where('leaves.type', $type))
            ->when($to, fn ($query) => $query->whereDate('leaves.start_date', '<=', $to))
            ->when($from, fn ($query) => $query->whereDate('leaves.end_date', '>=', $from))
            ->with([
                'employee' => fn ($query) => $query->with(Employee::AVATAR_RELATIONS),
                'leaveType',
                'media',
                'coverEmployee' => fn ($query) => $query->select(['id', 'contact_name', 'image_id', 'user_id'])->with(Employee::AVATAR_RELATIONS),
                'approver' => fn ($query) => $query->select(['id', 'contact_name', 'username', 'image_id'])->with('image'),
                'approvalRecords.approver' => fn ($query) => $query->select(['id', 'contact_name', 'username', 'image_id'])->with('image'),
            ])
            ->allowedFilters([$globalSearch])
            ->allowedSorts(['start_date', 'end_date', 'created_at', 'employee_name'])
            ->defaultSort('-created_at')
            ->withPaginator($prefix, tableName: 'grp.org.hr.leaves.index')
            ->withQueryString();
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', Rule::enum(LeaveStatusEnum::class)],
            'type'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'from'   => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'to'     => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array{status: string|null, type: string|null, from: string|null, to: string|null}
     */
    public function resolveFilters(ActionRequest $request): array
    {
        $legacyStatus = $request->input('leaves_filter.status');
        $legacyType   = $request->input('leaves_filter.type');

        return [
            'status' => ($this->validatedData['status'] ?? null)
                ?: (is_string($legacyStatus) ? LeaveStatusEnum::tryFrom($legacyStatus)?->value : null),
            'type'   => ($this->validatedData['type'] ?? null) ?: (is_string($legacyType) && $legacyType !== '' ? $legacyType : null),
            'from'   => $this->validatedData['from'] ?? null,
            'to'     => $this->validatedData['to'] ?? null,
        ];
    }

    public function jsonResponse(LengthAwarePaginator $leaves): AnonymousResourceCollection
    {
        return LeaveResource::collection($leaves);
    }

    public function htmlResponse(LengthAwarePaginator $leaves, ActionRequest $request): Response
    {
        $employees = Employee::where('organisation_id', $this->organisation->id)
            ->where('state', EmployeeStateEnum::WORKING)
            ->with([
                'jobPositions:job_positions.id,job_positions.name,job_positions.department',
                'users' => fn ($query) => $query->wherePivot('status', true)->where('users.status', true),
            ])
            ->orderBy('contact_name')
            ->get(['id', 'contact_name']);

        $canEditLeaves = $request->user()->authTo(["human-resources.{$this->organisation->id}.edit", "org-supervisor.{$this->organisation->id}.human-resources"]);

        return Inertia::render(
            'Org/HumanResources/LeaveAdmin',
            [
                'title' => __('Leave Requests'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'          => [
                        'icon'  => ['fal', 'fa-calendar-minus'],
                        'title' => __('Human resources')
                    ],
                    'iconRight'     => [
                        'icon'  => ['fal', 'fa-calendar-minus'],
                        'title' => __('Leave')
                    ],
                    'title'         => __('Leave Requests'),
                    'subNavigation' => $this->getLeaveSubNavigation($request),
                ],
                'leaves' => $leaves->through(fn (Leave $leave) => LeaveResource::make($leave)->resolve($request)),
                'filters' => [
                    'search' => (string) $request->input('filter.global', ''),
                    ...$this->resolveFilters($request),
                ],
                'type_options' => LeaveTypeResolver::optionsForOrganisation($this->organisation->id, false, $this->organisation->country?->code),
                'status_options' => LeaveStatusEnum::labels(),
                'can_edit_admin' => Auth::check() && LeaveApprover::query()
                    ->where('organisation_id', $this->organisation->id)
                    ->where('user_id', Auth::id())
                    ->where('is_active', true)
                    ->exists(),
                'holidays' => $this->getHolidayDates(),
                'can_record' => $canEditLeaves,
                'can_delete' => $canEditLeaves,
                'employee_options' => $employees->pluck('contact_name', 'id'),
                'employee_job_positions' => $employees->mapWithKeys(fn (Employee $employee) => [
                    $employee->id => $employee->jobPositions->map(fn (JobPosition $jobPosition) => $jobPosition->only(['id', 'name', 'department'])),
                ]),
                // ponytail: a few permission queries per employee, fine at tens of employees; precompute per user if an organisation grows to hundreds
                'employee_ids_with_group_access' => $employees
                    ->filter(fn (Employee $employee) => $employee->users->contains(fn (User $user) => $user->hasGroupAccess()))
                    ->pluck('id')
                    ->values(),
                // ponytail: the picker only knows leave from the last year; older dates are still checked on save
                'employee_leave_periods' => UpdateLeaveCover::approvedLeavePeriods($employees->pluck('id'), today()->subYear()),
            ]
        )->table($this->tableStructure());
    }

    private function getHolidayDates(): array
    {
        $now = Carbon::now();
        $holidays = Holiday::query()
            ->where('organisation_id', $this->organisation->id)
            ->where('to', '>=', $now->copy()->startOfYear())
            ->where('from', '<=', $now->copy()->addYear()->endOfYear())
            ->get();

        $dates = [];

        foreach ($holidays as $holiday) {
            $current = $holiday->from->copy();
            while ($current->lte($holiday->to)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($dates[$dateKey])) {
                    $dates[$dateKey] = ['date' => $dateKey, 'labels' => []];
                }
                if ($holiday->label) {
                    $dates[$dateKey]['labels'][] = $holiday->label;
                }
                $current->addDay();
            }
        }

        return array_values(array_map(
            fn (array $item): array => [
                'date'  => $item['date'],
                'label' => implode(', ', $item['labels']),
            ],
            $dates
        ));
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix . 'Page');
            }

            $table
                ->withGlobalSearch()
                ->column(
                    key: 'employee_name',
                    label: __('Employee'),
                    canBeHidden: false,
                    sortable: true,
                    searchable: true
                )
                ->column(
                    key: 'type_label',
                    label: __('Type'),
                    canBeHidden: false
                )
                ->column(
                    key: 'start_date',
                    label: __('Start Date'),
                    canBeHidden: false,
                    sortable: true
                )
                ->column(
                    key: 'end_date',
                    label: __('End Date'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'duration_days',
                    label: __('Days'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'status',
                    label: __('Status'),
                    canBeHidden: false,
                    sortable: true
                )
                ->column(
                    key: 'approval_progress',
                    label: __('Approval'),
                    canBeHidden: false
                )
                ->column(
                    key: 'reason',
                    label: __('Reason'),
                    canBeHidden: true,
                    sortable: true
                )
                ->column(
                    key: 'attachments',
                    label: __('Attachments'),
                    canBeHidden: false
                )
                ->column(
                    key: 'actions',
                    label: __('Options'),
                    canBeHidden: false
                )
                ->defaultSort('-created_at');
        };
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, null, $this->resolveFilters($request));
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
                        'label' => __('Leave Requests'),
                        'icon'  => 'fal fa-calendar-minus',
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.leaves.index' =>
            array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb($routeName, $routeParameters)
            ),
            default => [],
        };
    }
}
