<?php

namespace App\Actions\HumanResources\AttendanceAdjustment\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\HumanResources\Attendance\AttendanceAdjustmentStatusEnum;
use App\Http\Resources\HumanResources\AttendanceAdjustmentResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\AttendanceAdjustment;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexAttendanceAdjustmentsAdmin extends OrgAction
{
    use WithAdjustmentSubNavigation;
    use WithHumanResourcesAuthorisation;

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
            $term = '%'.addcslashes((string) $value, '%_\\').'%';

            $query->where(function ($query) use ($term) {
                $query->where('attendance_adjustments.employee_name', 'ilike', $term)
                    ->orWhere('attendance_adjustments.reason', 'ilike', $term);
            });
        });

        return QueryBuilder::for(AttendanceAdjustment::class)
            ->where('attendance_adjustments.organisation_id', $organisation->id)
            ->when($status, fn ($query) => $query->where('attendance_adjustments.status', $status))
            ->when($from, fn ($query) => $query->whereDate('attendance_adjustments.date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('attendance_adjustments.date', '<=', $to))
            ->with(['media', 'employee.image', 'employee.user.image', 'approver:id,contact_name,username,image_id', 'approver.image'])
            ->allowedFilters([$globalSearch])
            ->allowedSorts(['date', 'created_at', 'employee_name'])
            ->defaultSort('-created_at')
            ->withPaginator($prefix, tableName: 'grp.org.hr.adjustments.index')
            ->withQueryString();
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', Rule::enum(AttendanceAdjustmentStatusEnum::class)],
            'from'   => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'to'     => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function jsonResponse(LengthAwarePaginator $adjustments): AnonymousResourceCollection
    {
        return AttendanceAdjustmentResource::collection($adjustments);
    }

    public function htmlResponse(LengthAwarePaginator $adjustments, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/HumanResources/AttendanceAdjustmentAdmin',
            [
                'title' => __('Clocking adjustment'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'          => [
                        'icon'        => ['fal', 'fa-clock'],
                        'icon_layers' => [['fal', 'fa-clock'], ['fal', 'fa-pencil']],
                        'title'       => __('Clocking adjustment')
                    ],
                    'title'         => __('Clocking adjustment'),
                    'subNavigation' => $this->getAdjustmentSubNavigation($request),
                ],
                'adjustments' => $adjustments->through(
                    fn (AttendanceAdjustment $adjustment) => AttendanceAdjustmentResource::make($adjustment->setRelation('organisation', $this->organisation))->resolve($request)
                ),
                'filters' => [
                    'search' => (string) $request->input('filter.global', ''),
                    'status' => $this->validatedData['status'] ?? null,
                    'from'   => $this->validatedData['from'] ?? null,
                    'to'     => $this->validatedData['to'] ?? null,
                ],
                'can_edit' => $this->canEdit,
                'status_options' => AttendanceAdjustmentStatusEnum::labels(),
            ]
        )->table($this->tableStructure());
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix . 'Page');
            }

            $table
                ->withGlobalSearch()
                ->column(key: 'employee_name', label: __('Employee'), sortable: true)
                ->column(key: 'date', label: __('Date'), sortable: true)
                ->column(key: 'original_times', label: __('Original Times'))
                ->column(key: 'requested_times', label: __('Requested Times'))
                ->column(key: 'status_label', label: __('Status'))
                ->column(key: 'reason', label: __('Reason'))
                ->column(key: 'attachments', label: __('Attachments'))
                ->column(key: 'actions', label: 'Actions')
                ->defaultSort('-created_at');
        };
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
                        'label' => __('Clocking adjustment'),
                        'icon'  => 'fal fa-clock',
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.adjustments.index' =>
            array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb($routeName, $routeParameters)
            ),
            default => [],
        };
    }
}
