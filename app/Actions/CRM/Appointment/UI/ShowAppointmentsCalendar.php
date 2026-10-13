<?php

namespace App\Actions\CRM\Appointment\UI;

use App\Actions\CRM\AppointmentType\UI\WithAppointmentsSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithCRMAuthorisation;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowAppointmentsCalendar extends OrgAction
{
    use WithCRMAuthorisation;
    use WithAppointmentsSubNavigation;

    /**
     * One month as whole weeks, Monday first, in the shop's timezone. Declined and cancelled
     * appointments are left out: they no longer take any time.
     *
     * @return array{
     *     month: string,
     *     label: string,
     *     previous: string,
     *     next: string,
     *     current: string,
     *     timezone: string,
     *     weekdays: array<int, string>,
     *     total: int,
     *     weeks: array<int, array<int, array{date: string, day: int, in_month: bool, is_today: bool, appointments: array<int, array<string, mixed>>}>>
     * }
     */
    public function handle(Shop $shop, ?string $month = null): array
    {
        $timezone   = $shop->timezone?->name ?? 'UTC';
        $today      = now($timezone)->startOfDay();
        $monthStart = $month ? Carbon::createFromFormat('!Y-m', $month, $timezone) : $today->copy()->startOfMonth();
        $firstDay   = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $lastDay    = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $appointments = $shop->appointments()
            ->whereNotIn('state', AppointmentStateEnum::closed())
            ->where('starts_at', '>=', $firstDay->copy()->utc())
            ->where('starts_at', '<=', $lastDay->copy()->utc())
            ->with(['appointmentType', 'user'])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->starts_at->copy()->setTimezone($timezone)->toDateString());

        $weeks = [];
        for ($day = $firstDay->copy(), $position = 0; $day->lte($lastDay); $day->addDay(), $position++) {
            $weeks[intdiv($position, 7)][] = [
                'date'         => $day->toDateString(),
                'day'          => $day->day,
                'in_month'     => $day->isSameMonth($monthStart),
                'is_today'     => $day->isSameDay($today),
                'appointments' => $appointments->get($day->toDateString(), collect())
                    ->map(fn (Appointment $appointment) => [
                        'id'                    => $appointment->id,
                        'time'                  => $appointment->starts_at->copy()->setTimezone($timezone)->format('H:i'),
                        'contact_name'          => $appointment->contact_name,
                        'appointment_type_name' => $appointment->appointmentType?->name,
                        'staff_name'            => $appointment->user?->contact_name,
                        'state'                 => $appointment->state->value,
                        'state_label'           => $appointment->state->label(),
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return [
            'month'    => $monthStart->format('Y-m'),
            'label'    => $monthStart->translatedFormat('F Y'),
            'previous' => $monthStart->copy()->subMonthNoOverflow()->format('Y-m'),
            'next'     => $monthStart->copy()->addMonthNoOverflow()->format('Y-m'),
            'current'  => $today->format('Y-m'),
            'timezone' => $timezone,
            'weekdays' => collect(range(0, 6))->map(fn (int $offset) => $firstDay->copy()->addDays($offset)->translatedFormat('D'))->all(),
            'total'    => $appointments->flatten(1)->filter(fn (Appointment $appointment) => $appointment->starts_at->copy()->setTimezone($timezone)->isSameMonth($monthStart))->count(),
            'weeks'    => $weeks,
        ];
    }

    public function rules(): array
    {
        return [
            'month' => ['sometimes', 'nullable', 'date_format:Y-m'],
        ];
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): array
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData['month'] ?? null);
    }

    public function htmlResponse(array $calendar, ActionRequest $request): Response
    {
        $routeParameters = [
            'organisation' => $this->organisation->slug,
            'shop'         => $this->shop->slug,
        ];

        $actions = [];
        if ($this->canEdit) {
            $actions[] = [
                'type'    => 'button',
                'style'   => 'create',
                'tooltip' => __('New appointment'),
                'label'   => __('Appointment'),
                'route'   => [
                    'name'       => 'grp.org.shops.show.crm.appointments.create',
                    'parameters' => $routeParameters,
                ],
            ];
        }

        return Inertia::render(
            'Org/Shop/CRM/AppointmentsCalendar',
            [
                'breadcrumbs' => $this->getBreadcrumbs($routeParameters),
                'title'       => __('Appointments calendar'),
                'pageHead'    => [
                    'title'         => __('Appointments calendar'),
                    'icon'          => [
                        'icon'  => ['fal', 'fa-calendar-alt'],
                        'title' => __('Appointments calendar'),
                    ],
                    'actions'       => $actions,
                    'subNavigation' => $this->getAppointmentsSubNavigation($this->shop),
                ],
                'calendar'    => $calendar,
                'routes'      => [
                    'calendar' => [
                        'name'       => 'grp.org.shops.show.crm.appointments.calendar',
                        'parameters' => $routeParameters,
                    ],
                    'show'     => [
                        'name'       => 'grp.org.shops.show.crm.appointments.show',
                        'parameters' => $routeParameters,
                    ],
                ],
            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexAppointments::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.shops.show.crm.appointments.calendar',
                            'parameters' => [
                                'organisation' => $routeParameters['organisation'],
                                'shop'         => $routeParameters['shop'],
                            ],
                        ],
                        'label' => __('Calendar'),
                    ],
                ],
            ]
        );
    }
}
