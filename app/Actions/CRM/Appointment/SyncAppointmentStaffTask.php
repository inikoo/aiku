<?php

namespace App\Actions\CRM\Appointment;

use App\Actions\Tasks\GetAikuAssistant;
use App\Actions\Tasks\StoreStaffTask;
use App\Actions\Tasks\UpdateStaffTask;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\Tasks\StaffTaskStatusEnum;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentType;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * An accepted appointment that has a member of staff is one open task for that person, so it
 * reaches them as a notification and sits in their task list until the day. The task follows the
 * appointment: it moves when the appointment is moved or handed to someone else, and closes when
 * the appointment is declined, cancelled or left without staff.
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

        if (!$staff) {
            return $task
                ? UpdateStaffTask::run($task, $assistant, [
                    'status' => StaffTaskStatusEnum::CANCELLED->value,
                    'note'   => $this->closingNote($appointment),
                ])
                : null;
        }

        $subject     = $this->subject($appointment);
        $description = $this->description($appointment);
        $dueAt       = $appointment->starts_at->copy()->setTimezone($this->timezone($appointment))->toDateString();

        if (!$task) {
            return StoreStaffTask::run($assistant, [
                'subject'     => $subject,
                'description' => $description,
                'assignee_id' => $staff->id,
                'due_at'      => $dueAt,
                'data'        => [
                    'kind'            => self::TASK_KIND,
                    'appointment_id'  => $appointment->id,
                    'shop_id'         => $appointment->shop_id,
                    'organisation_id' => $appointment->organisation_id,
                ],
            ]);
        }

        if ($task->subject === $subject && $task->description === $description && $task->assignee_id === $staff->id) {
            return $task;
        }

        return UpdateStaffTask::run($task, $assistant, [
            'subject'     => $subject,
            'description' => $description,
            'assignee_id' => $staff->id,
            'due_at'      => $dueAt,
            'note'        => $task->subject !== $subject ? __('The appointment changed: :subject', ['subject' => $subject]) : null,
        ]);
    }

    private function staffToRemind(Appointment $appointment): ?User
    {
        if ($appointment->state !== AppointmentStateEnum::ACCEPTED || $appointment->ends_at->isPast()) {
            return null;
        }

        $staff = $appointment->user;

        return $staff && $staff->status && StaffTask::canBeAssigned($staff) ? $staff : null;
    }

    private function closingNote(Appointment $appointment): string
    {
        return match (true) {
            $appointment->state === AppointmentStateEnum::DECLINED  => __('The appointment was declined'),
            $appointment->state === AppointmentStateEnum::CANCELLED => __('The appointment was cancelled'),
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
        return mb_substr(__(':type with :visitor, :when', [
            'type'    => $appointment->appointmentType->name,
            'visitor' => $appointment->contact_name,
            'when'    => $this->when($appointment),
        ]), 0, 255);
    }

    private function where(AppointmentType $appointmentType): ?string
    {
        return $appointmentType->media()?->label() ?? $appointmentType->location;
    }

    private function description(Appointment $appointment): string
    {
        $appointmentType = $appointment->appointmentType;

        $lines = array_filter([
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
