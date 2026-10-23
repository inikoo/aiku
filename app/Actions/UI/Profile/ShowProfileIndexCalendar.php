<?php

namespace App\Actions\UI\Profile;

use App\Actions\CRM\Appointment\SyncAppointmentStaffTask;
use App\Actions\OrgAction;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\Helpers\Ticket\TicketStatusEnum;
use App\Enums\Tasks\StaffTaskStatusEnum;
use App\Enums\UI\SysAdmin\ProfileTabsEnum;
use App\Models\CRM\Appointment;
use App\Models\Helpers\Ticket;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

/**
 * What is on the user's plate, day by day: the appointments they arrange, the deadlines of their
 * staff tasks, and for their tickets the milestone due date or the day a waiting ticket comes back.
 */
class ShowProfileIndexCalendar extends OrgAction
{
    /**
     * @return array{
     *     month: string,
     *     label: string,
     *     previous: string,
     *     next: string,
     *     current: string,
     *     timezone: string,
     *     weekdays: array<int, string>,
     *     counts: array{appointment: int, task: int, ticket: int},
     *     weeks: array<int, array<int, array{date: string, day: int, in_month: bool, is_today: bool, entries: array<int, array<string, mixed>>}>>
     * }
     */
    public function handle(User $user, ?string $month = null): array
    {
        $timezone   = $user->timezone_name;
        $today      = now($timezone)->startOfDay();
        $monthStart = $month ? Carbon::createFromFormat('!Y-m', $month, $timezone) : $today->copy()->startOfMonth();
        $firstDay   = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $lastDay    = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $from       = $firstDay->copy()->utc();
        $to         = $lastDay->copy()->utc();

        $entries = $this->appointments($user, $from, $to, $timezone)
            ->concat($this->tasks($user, $firstDay, $lastDay))
            ->concat($this->tickets($user, $firstDay, $lastDay))
            ->sortBy(fn (array $entry) => $entry['date'].' '.($entry['time'] ?? '99:99'))
            ->groupBy('date');

        $weeks = [];
        for ($day = $firstDay->copy(), $position = 0; $day->lte($lastDay); $day->addDay(), $position++) {
            $weeks[intdiv($position, 7)][] = [
                'date'     => $day->toDateString(),
                'day'      => $day->day,
                'in_month' => $day->isSameMonth($monthStart),
                'is_today' => $day->isSameDay($today),
                'entries'  => $entries->get($day->toDateString(), collect())->values()->all(),
            ];
        }

        $inMonth = $entries->filter(fn (Collection $dayEntries, string $date) => Carbon::parse($date, $timezone)->isSameMonth($monthStart))->flatten(1);

        return [
            'month'    => $monthStart->format('Y-m'),
            'label'    => $monthStart->translatedFormat('F Y'),
            'previous' => $monthStart->copy()->subMonthNoOverflow()->format('Y-m'),
            'next'     => $monthStart->copy()->addMonthNoOverflow()->format('Y-m'),
            'current'  => $today->format('Y-m'),
            'timezone' => $timezone,
            'weekdays' => collect(range(0, 6))->map(fn (int $offset) => $firstDay->copy()->addDays($offset)->translatedFormat('D'))->all(),
            'counts'   => [
                'appointment' => $inMonth->where('kind', 'appointment')->count(),
                'task'        => $inMonth->where('kind', 'task')->count(),
                'ticket'      => $inMonth->where('kind', 'ticket')->count(),
            ],
            'weeks'    => $weeks,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function appointments(User $user, Carbon $from, Carbon $to, string $timezone): Collection
    {
        return Appointment::where('user_id', $user->id)
            ->whereNotIn('state', AppointmentStateEnum::closed())
            ->whereBetween('starts_at', [$from, $to])
            ->with(['appointmentType', 'shop', 'organisation'])
            ->get()
            ->map(function (Appointment $appointment) use ($timezone) {
                $startsAt = $appointment->starts_at->copy()->setTimezone($timezone);

                return [
                    'kind'     => 'appointment',
                    'key'      => 'appointment-'.$appointment->id,
                    'date'     => $startsAt->toDateString(),
                    'time'     => $startsAt->format('H:i'),
                    'title'    => $appointment->contact_name,
                    'subtitle' => collect([$appointment->appointmentType?->name, $appointment->shop->name, $startsAt->format('H:i').'–'.$appointment->ends_at->copy()->setTimezone($timezone)->format('H:i')])->filter()->implode(' · '),
                    'state'    => $appointment->state->value,
                    'is_done'  => in_array($appointment->state, AppointmentStateEnum::finished(), true),
                    'url'      => route('grp.org.shops.show.crm.appointments.show', [$appointment->organisation->slug, $appointment->shop->slug, $appointment->id]),
                ];
            });
    }

    /**
     * A task deadline is a day, not a time. Appointment tasks are left out: the appointment itself
     * is already on the calendar.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function tasks(User $user, Carbon $firstDay, Carbon $lastDay): Collection
    {
        return StaffTask::where('assignee_id', $user->id)
            ->where('status', '!=', StaffTaskStatusEnum::CANCELLED)
            ->whereBetween('due_at', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->where(fn ($query) => $query->whereNull('data->kind')->orWhere('data->kind', '!=', SyncAppointmentStaffTask::TASK_KIND))
            ->get()
            ->map(fn (StaffTask $task) => [
                'kind'       => 'task',
                'key'        => 'task-'.$task->id,
                'date'       => $task->due_at->toDateString(),
                'time'       => null,
                'title'      => $task->reference.' '.$task->subject,
                'subtitle'   => __('Deadline'),
                'state'      => $task->status->value,
                'is_done'    => $task->status === StaffTaskStatusEnum::DONE,
                'is_overdue' => $task->isOpen() && $task->due_at->copy()->endOfDay()->isPast(),
                'url'        => route('grp.tasks.show', $task->reference),
            ]);
    }

    /**
     * A ticket has no deadline of its own: it is due when its project milestone is, and a ticket
     * put on hold comes back on its waiting-until day.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function tickets(User $user, Carbon $firstDay, Carbon $lastDay): Collection
    {
        $open = Ticket::where('assignee_id', $user->id)
            ->whereNotIn('status', [TicketStatusEnum::RESOLVED, TicketStatusEnum::CANCELLED]);

        $milestoneDue = (clone $open)
            ->join('ticket_project_milestones', 'ticket_project_milestones.id', '=', 'tickets.ticket_project_milestone_id')
            ->whereNull('ticket_project_milestones.done_at')
            ->whereBetween('ticket_project_milestones.due_date', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->get(['tickets.id', 'tickets.reference', 'tickets.subject', 'tickets.status', 'ticket_project_milestones.name as milestone_name', 'ticket_project_milestones.due_date as milestone_due_date'])
            ->map(fn (Ticket $ticket) => [
                'kind'       => 'ticket',
                'key'        => 'ticket-due-'.$ticket->id,
                'date'       => Carbon::parse($ticket->milestone_due_date)->toDateString(),
                'time'       => null,
                'title'      => $ticket->reference.' '.$ticket->subject,
                'subtitle'   => __('Milestone due: :milestone', ['milestone' => $ticket->milestone_name]),
                'state'      => $ticket->status->value,
                'is_done'    => false,
                'is_overdue' => Carbon::parse($ticket->milestone_due_date)->endOfDay()->isPast(),
                'url'        => route('grp.tickets.show', $ticket->reference),
            ]);

        $comingBack = (clone $open)
            ->where('status', TicketStatusEnum::WAITING)
            ->whereBetween('waiting_until', [$firstDay->copy()->utc(), $lastDay->copy()->utc()])
            ->get(['id', 'reference', 'subject', 'status', 'waiting_until'])
            ->map(fn (Ticket $ticket) => [
                'kind'       => 'ticket',
                'key'        => 'ticket-waiting-'.$ticket->id,
                'date'       => $ticket->waiting_until->copy()->setTimezone($firstDay->timezone)->toDateString(),
                'time'       => null,
                'title'      => $ticket->reference.' '.$ticket->subject,
                'subtitle'   => __('Back from waiting'),
                'state'      => $ticket->status->value,
                'is_done'    => false,
                'is_overdue' => false,
                'url'        => route('grp.tickets.show', $ticket->reference),
            ]);

        return $milestoneDue->concat($comingBack);
    }

    public function rules(): array
    {
        return [
            'month' => ['sometimes', 'nullable', 'date_format:Y-m'],
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $this->initialisationFromGroup(group(), $request)->withTab(ProfileTabsEnum::values());

        return $this->handle($request->user(), $this->validatedData['month'] ?? null);
    }

    public function jsonResponse(array $calendar): array
    {
        return $calendar;
    }
}
