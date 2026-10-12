<?php

namespace App\Actions\HumanResources\EmployeeAnalytics\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use App\Services\EmployeeAnalyticsService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexEmployeeAnalytics extends OrgAction
{
    use WithHumanResourcesAuthorisation;

    protected EmployeeAnalyticsService $service;

    public function __construct()
    {
        $this->service = new EmployeeAnalyticsService();
    }

    public function handle(Organisation $organisation, Carbon $startDate, Carbon $endDate): object|null
    {
        return $this->service->getOrganizationAnalyticsAggregated($organisation->id, $startDate, $endDate);
    }

    public function getAttendanceBreakdown(Organisation $organisation, Carbon $startDate, Carbon $endDate): array
    {
        return $this->service->getEmployeeAttendanceBreakdown($organisation->id, $startDate, $endDate);
    }

    public function getTopEmployeesByLeave(Organisation $organisation, Carbon $startDate, Carbon $endDate): array
    {
        return $this->service->getTopEmployeesByLeave($organisation->id, $startDate, $endDate);
    }

    /**
     * @return array{start: string, end: string|null, today: string, status: string, working_days: int, employees_measured: int}
     */
    public function getAttendanceWindow(Organisation $organisation, Carbon $startDate, Carbon $endDate): array
    {
        return $this->service->getAttendanceWindow($organisation->id, $startDate, $endDate);
    }

    /**
     * @param  array<int, array<string, mixed>>  ...$employeeLists
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function withAvatars(array ...$employeeLists): array
    {
        $avatars = Employee::avatarUrlsFor(collect($employeeLists)->flatten(1)->pluck('id'));

        return array_map(
            fn (array $employees): array => array_map(
                fn (array $employee): array => array_merge($employee, ['avatar' => $avatars->get($employee['id'] ?? null)]),
                $employees
            ),
            $employeeLists
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        if (!config('employee-analytics.enabled', false)) {
            abort(404, 'Employee Analytics feature is not enabled.');
        }

        $this->initialisation($organisation, $request);

        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()->format('Y-m-d')));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()->format('Y-m-d')));

        $analytics = $this->handle($organisation, $startDate, $endDate);
        $attendanceBreakdown = $this->getAttendanceBreakdown($organisation, $startDate, $endDate);
        $topEmployeesByLeave = $this->getTopEmployeesByLeave($organisation, $startDate, $endDate);
        [$attendanceBreakdown, $topEmployeesByLeave] = $this->withAvatars($attendanceBreakdown, $topEmployeesByLeave);

        return Inertia::render(
            'Org/HumanResources/EmployeeAnalytics',
            [
                'title'       => __('Employee Analytics'),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'pageHead'    => [
                    'icon'  => [
                        'icon'  => 'fal fa-chart-line',
                        'title' => __('Analytics'),
                    ],
                    'title' => __('Employee Analytics'),
                ],
                'filters'     => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date'   => $endDate->format('Y-m-d'),
                ],
                'analytics'   => $analytics,
                'total_employees' => $organisation->humanResourcesStats->number_employees_state_working ?? 0,
                'attendance_window' => $this->getAttendanceWindow($organisation, $startDate, $endDate),
                'attendance_breakdown' => $attendanceBreakdown,
                'top_employees_by_leave' => $topEmployeesByLeave,
            ]
        );
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
                            'name'       => 'grp.org.hr.analytics.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Analytics'),
                        'icon'  => 'fal fa-chart-line',
                    ],
                ],
            ]
        );
    }
}
