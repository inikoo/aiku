<?php

namespace App\Actions\CRM\Appointment\UI;

use App\Actions\CRM\AppointmentType\UI\WithAppointmentsSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithCRMAuthorisation;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentBlock;
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
     * appointments are left out: they no longer take any time. A block shows on every day it
     * covers, and an appointment still booked inside one is flagged.
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
     *     weeks: array<int, array<int, array{date: string, day: int, in_month: bool, is_today: bool, blocks: array<int, array<string, mixed>>, appointments: array<int, array<string, mixed>>}>>
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

        $blocks = $shop->appointmentBlocks()
            ->overlapping($firstDay->copy()->utc(), $lastDay->copy()->addDay()->utc())
            ->with(['appointmentType', 'createdBy'])
            ->orderBy('starts_at')
            ->get();

        $isBlocked = fn (Appointment $appointment) => $appointment->state->holdsSlot() && $blocks->contains(
            fn (AppointmentBlock $block) => $block->affects($appointment->appointment_type_id)
                && $block->starts_at->lt($appointment->ends_at)
                && $block->ends_at->gt($appointment->starts_at)
        );

        $weeks = [];
        for ($day = $firstDay->copy(), $position = 0; $day->lte($lastDay); $day->addDay(), $position++) {
            $weeks[intdiv($position, 7)][] = [
                'date'         => $day->toDateString(),
                'day'          => $day->day,
                'in_month'     => $day->isSameMonth($monthStart),
                'is_today'     => $day->isSameDay($today),
                'blocks'       => $blocks
                    ->filter(fn (AppointmentBlock $block) => $block->starts_at->lt($day->copy()->addDay()) && $block->ends_at->gt($day))
                    ->map(fn (AppointmentBlock $block) => $this->blockData($block, $day, $timezone))
                    ->values()
                    ->all(),
                'appointments' => $appointments->get($day->toDateString(), collect())
                    ->map(fn (Appointment $appointment) => [
                        'id'                    => $appointment->id,
                        'time'                  => $appointment->starts_at->copy()->setTimezone($timezone)->format('H:i'),
                        'ends_time'             => $appointment->ends_at->copy()->setTimezone($timezone)->format('H:i'),
                        'date_label'            => $appointment->starts_at->copy()->setTimezone($timezone)->translatedFormat('l j F Y'),
                        'contact_name'          => $appointment->contact_name,
                        'email'                 => $appointment->email,
                        'phone'                 => $appointment->phone,
                        'appointment_type_name' => $appointment->appointmentType?->name,
                        'staff_name'            => $appointment->user?->contact_name,
                        'state'                 => $appointment->state->value,
                        'state_label'           => $appointment->state->label(),
                        'is_blocked'            => $isBlocked($appointment),
                        'can_accept'            => $appointment->state === AppointmentStateEnum::REQUESTED,
                        'can_decline'           => $appointment->state->holdsSlot(),
                        'can_reschedule'        => $appointment->state->holdsSlot(),
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

    /**
     * @return array<string, mixed>
     */
    private function blockData(AppointmentBlock $block, Carbon $day, string $timezone): array
    {
        $startsAt = $block->starts_at->copy()->setTimezone($timezone);
        $endsAt   = $block->ends_at->copy()->setTimezone($timezone);

        $coversWholeDay = $startsAt->lte($day) && $endsAt->gte($day->copy()->addDay());

        return [
            'id'                    => $block->id,
            'is_all_day'            => $coversWholeDay,
            'time'                  => $coversWholeDay ? null : $startsAt->max($day)->format('H:i').'–'.($endsAt->gte($day->copy()->addDay()) ? '24:00' : $endsAt->format('H:i')),
            'period_label'          => $block->is_all_day
                ? $startsAt->translatedFormat('D j M Y').($endsAt->copy()->subDay()->isSameDay($startsAt) ? '' : ' – '.$endsAt->copy()->subDay()->translatedFormat('D j M Y'))
                : $startsAt->translatedFormat('D j M Y H:i').'–'.$endsAt->format('H:i'),
            'reason'                => $block->reason,
            'appointment_type_name' => $block->appointmentType?->name,
            'created_by'            => $block->createdBy?->contact_name,
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
                'canEdit'     => $this->canEdit,
                'appointmentTypes' => $this->shop->appointmentTypes()->orderBy('name')->get(['id', 'name'])->map(fn ($type) => ['id' => $type->id, 'name' => $type->name])->all(),
                'routes'      => [
                    'calendar' => [
                        'name'       => 'grp.org.shops.show.crm.appointments.calendar',
                        'parameters' => $routeParameters,
                    ],
                    'show'     => [
                        'name'       => 'grp.org.shops.show.crm.appointments.show',
                        'parameters' => $routeParameters,
                    ],
                    'store_block'  => [
                        'name'       => 'grp.models.shop.appointment_block.store',
                        'parameters' => ['shop' => $this->shop->id],
                    ],
                    'delete_block' => [
                        'name'       => 'grp.models.shop.appointment_block.delete',
                        'parameters' => ['shop' => $this->shop->id],
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
