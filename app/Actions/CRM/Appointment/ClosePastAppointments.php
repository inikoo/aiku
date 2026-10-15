<?php

namespace App\Actions\CRM\Appointment;

use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use App\Models\Tasks\StaffTask;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Once its time has passed an accepted appointment is taken as having happened, so it reads
 * Completed and its task is done without anyone touching it; staff change it to No show when the
 * visitor did not come. A request nobody answered in time keeps its state, and only its task is
 * closed.
 */
class ClosePastAppointments
{
    use AsAction;

    public string $commandSignature = 'appointments:close-past';

    /**
     * @return array{completed: int, tasks_closed: int}
     */
    public function handle(): array
    {
        $completed = 0;
        Appointment::where('state', AppointmentStateEnum::ACCEPTED)
            ->where('ends_at', '<', now())
            ->eachById(function (Appointment $appointment) use (&$completed) {
                UpdateAppointment::make()->action($appointment, ['state' => AppointmentStateEnum::COMPLETED->value]);
                $completed++;
            });

        $tasksClosed = 0;
        StaffTask::open()
            ->where('data->kind', SyncAppointmentStaffTask::TASK_KIND)
            ->eachById(function (StaffTask $task) use (&$tasksClosed) {
                $appointment = Appointment::find($task->data['appointment_id'] ?? null);

                if ($appointment?->ends_at->isPast()) {
                    SyncAppointmentStaffTask::run($appointment);
                    $tasksClosed++;
                }
            });

        return ['completed' => $completed, 'tasks_closed' => $tasksClosed];
    }

    public function asCommand(Command $command): int
    {
        $closed = $this->handle();

        $command->info($closed['completed'].' appointments completed, '.$closed['tasks_closed'].' tasks of unanswered requests closed');

        return 0;
    }
}
