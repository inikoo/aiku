<?php

namespace App\Actions\CRM\Appointment;

use App\Actions\Tasks\GetAikuAssistant;
use App\Actions\Tasks\StoreStaffTask;
use App\Actions\Tasks\SyncStaffTaskCollaborators;
use App\Actions\Tasks\UpdateStaffTask;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\Tasks\StaffTaskStatusEnum;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentType;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * An appointment that still has to happen is one open task, so it reaches staff as a notification
 * and sits in their task list until the day. A request waiting for an answer is a task for
 * everyone who arranges that type of appointment; once accepted it is the task of the member of
 * staff it was given to. The task follows the appointment: it moves when the appointment is moved
 * or handed to someone else, is done when the visit took place, and closes when the appointment is
 * declined, cancelled, passed or left without staff.
 */
class SyncAppointmentStaffTask
{
    use AsAction;

    public const string TASK_KIND = 'appointment';

    public function handle(Appointment $appointment): ?StaffTask
    {
        $assistant = GetAikuAssistant::run($appointment->group_id);
        $task      = StaffTask::open()
            ->where('data->kind', self::TASK_KIND)
            ->where('data->appointment_id', $appointment->id)
            ->first();
        $staff     = $this->staffToRemind($appointment);

        if ($staff->isEmpty()) {
            return $task
                ? UpdateStaffTask::run($task, $assistant, [
                    'status' => $appointment->state === AppointmentStateEnum::COMPLETED ? StaffTaskStatusEnum::DONE->value : StaffTaskStatusEnum::CANCELLED->value,
                    'note'   => $this->closingNote($appointment),
                ])
                : null;
        }

        /** @var User $assignee */
        $assignee        = $staff->first();
        $collaboratorIds = $staff->skip(1)->pluck('id')->all();
        $subject         = $this->subject($appointment);
        $description     = $this->description($appointment);
        $dueAt           = $appointment->starts_at->copy()->setTimezone($this->timezone($appointment))->toDateString();

        if (!$task) {
            return StoreStaffTask::run($assistant, [
                'subject'          => $subject,
                'description'      => $description,
                'assignee_id'      => $assignee->id,
                'collaborator_ids' => $collaboratorIds,
                'due_at'           => $dueAt,
                'data'             => [
                    'kind'            => self::TASK_KIND,
                    'appointment_id'  => $appointment->id,
                    'shop_id'         => $appointment->shop_id,
                    'organisation_id' => $appointment->organisation_id,
                ],
            ]);
        }

        if ($task->subject !== $subject || $task->description !== $description || $task->assignee_id !== $assignee->id) {
            $task = UpdateStaffTask::run($task, $assistant, [
                'subject'     => $subject,
                'description' => $description,
                'assignee_id' => $assignee->id,
                'due_at'      => $dueAt,
                'note'        => $task->subject !== $subject ? __('The appointment changed: :subject', ['subject' => $subject]) : null,
            ]);
        }

        return SyncStaffTaskCollaborators::run($task, $collaboratorIds, $assistant);
    }

    /**
     * @return Collection<int, User> the first is who the task is for, the others work on it too
     */
    public function staffToRemind(Appointment $appointment): Collection
    {
        if ($appointment->ends_at->isPast()) {
            return collect();
        }

        $staff = match ($appointment->state) {
            AppointmentStateEnum::ACCEPTED  => collect([$appointment->user]),
            AppointmentStateEnum::REQUESTED => $appointment->appointmentType?->attendees()->orderBy('users.id')->get() ?? collect(),
            default                         => collect(),
        };

        return $staff
            ->filter(fn (?User $user) => $user && $user->status && StaffTask::canBeAssigned($user))
            ->values();
    }

    private function closingNote(Appointment $appointment): string
    {
        return match (true) {
            $appointment->state === AppointmentStateEnum::DECLINED  => __('The appointment was declined'),
            $appointment->state === AppointmentStateEnum::CANCELLED => __('The appointment was cancelled'),
            $appointment->state === AppointmentStateEnum::COMPLETED => __('The visit took place'),
            $appointment->state === AppointmentStateEnum::NO_SHOW   => __('The visitor did not come'),
            $appointment->ends_at->isPast()                         => __('The appointment has passed'),
            default                                                 => __('The appointment is no longer yours'),
        };
    }

    private function timezone(Appointment $appointment): string
    {
        return $appointment->shop->timezone?->name ?? 'UTC';
    }

    private function when(Appointment $appointment): string
    {
        $timezone = $this->timezone($appointment);

        return $appointment->starts_at->copy()->setTimezone($timezone)->translatedFormat('D j M Y, H:i')
            .'–'.$appointment->ends_at->copy()->setTimezone($timezone)->format('H:i');
    }

    private function subject(Appointment $appointment): string
    {
        $replacements = [
            'type'    => $appointment->appointmentType->name,
            'visitor' => $appointment->contact_name,
            'when'    => $this->when($appointment),
        ];

        return mb_substr(
            $appointment->state === AppointmentStateEnum::REQUESTED
                ? __('Appointment request: :type with :visitor, :when', $replacements)
                : __(':type with :visitor, :when', $replacements),
            0,
            255
        );
    }

    private function where(AppointmentType $appointmentType): ?string
    {
        return $appointmentType->media()?->label() ?? $appointmentType->location;
    }

    private function description(Appointment $appointment): string
    {
        $appointmentType = $appointment->appointmentType;

        $lines = array_filter([
            $appointment->state === AppointmentStateEnum::REQUESTED ? __('Booked on the website and waiting for an answer: accept it, decline it or offer another time.') : null,
            __('When').': '.$this->when($appointment).' ('.$this->timezone($appointment).')',
            __('Type').': '.$appointmentType->name.' · '.$appointmentType->meeting_mode->label(),
            $this->where($appointmentType) ? __('Where').': '.$this->where($appointmentType) : null,
            __('Visitor').': '.implode(' · ', array_filter([$appointment->contact_name, $appointment->email, $appointment->phone])),
            $appointment->number_visitors > 1 ? __('People coming').': '.$appointment->number_visitors : null,
            $appointment->notes ? __('Notes').': '.$appointment->notes : null,
        ]);

        $url = route('grp.org.shops.show.crm.appointments.show', [
            $appointment->organisation->slug,
            $appointment->shop->slug,
            $appointment->id,
        ]);

        return mb_substr(implode("\n", $lines), 0, 4500)."\n\n".__('Appointment').': '.$url;
    }
}
