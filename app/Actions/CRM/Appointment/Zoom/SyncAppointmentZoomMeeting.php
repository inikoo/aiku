<?php

namespace App\Actions\CRM\Appointment\Zoom;

use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncAppointmentZoomMeeting
{
    use AsAction;

    public string $jobQueue = 'default';

    public int $jobTries = 3;

    public int $jobBackoff = 60;

    /**
     * @throws RequestException
     */
    public function handle(int $appointmentId): ?Appointment
    {
        return Cache::lock('appointment-zoom-'.$appointmentId, 30)->block(20, function () use ($appointmentId) {
            $appointment = Appointment::with(['shop', 'appointmentType'])->find($appointmentId);

            if (!$appointment) {
                return null;
            }

            $meeting = Arr::get($appointment->data, 'zoom');

            if ($this->needsMeeting($appointment)) {
                $wanted = $this->meetingTimes($appointment);

                if (!$meeting) {
                    $created = ZoomClient::make()->createMeeting($appointment->shop, $this->meetingPayload($appointment));

                    return $this->storeMeeting($appointment, [
                        'meeting_id' => (string) $created['id'],
                        'join_url'   => $created['join_url'],
                        ...$wanted,
                    ]);
                }

                if (Arr::only($meeting, ['start_time', 'duration']) != $wanted) {
                    ZoomClient::make()->updateMeeting($appointment->shop, $meeting['meeting_id'], $this->meetingPayload($appointment));

                    return $this->storeMeeting($appointment, [...$meeting, ...$wanted]);
                }

                return $appointment;
            }

            if ($meeting && $this->shouldRemoveMeeting($appointment)) {
                if (ZoomClient::make()->isConfigured($appointment->shop)) {
                    ZoomClient::make()->deleteMeeting($appointment->shop, $meeting['meeting_id']);
                }

                return $this->storeMeeting($appointment, null);
            }

            return $appointment;
        });
    }

    public function needsMeeting(Appointment $appointment): bool
    {
        return $appointment->state === AppointmentStateEnum::ACCEPTED
            && $appointment->ends_at->isFuture()
            && (bool) $appointment->appointmentType?->isZoomCall()
            && ZoomClient::make()->isConfigured($appointment->shop);
    }

    private function shouldRemoveMeeting(Appointment $appointment): bool
    {
        return in_array($appointment->state, [AppointmentStateEnum::DECLINED, AppointmentStateEnum::CANCELLED, AppointmentStateEnum::REQUESTED], true)
            || !$appointment->appointmentType?->isZoomCall();
    }

    /**
     * @return array{start_time: string, duration: int}
     */
    private function meetingTimes(Appointment $appointment): array
    {
        return [
            'start_time' => $appointment->starts_at->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'duration'   => (int) $appointment->starts_at->diffInMinutes($appointment->ends_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function meetingPayload(Appointment $appointment): array
    {
        return [
            'topic'    => ($appointment->appointmentType?->name ?? __('Appointment')).' · '.$appointment->shop->name,
            'type'     => 2,
            ...$this->meetingTimes($appointment),
            'timezone' => $appointment->shop->timezone?->name ?? 'UTC',
            'settings' => [
                'join_before_host' => false,
                'waiting_room'     => true,
            ],
        ];
    }

    private function storeMeeting(Appointment $appointment, ?array $meeting): Appointment
    {
        $data = $appointment->data ?? [];

        if ($meeting) {
            $data['zoom'] = $meeting;
        } else {
            unset($data['zoom']);
        }

        $appointment->updateQuietly(['data' => $data]);

        return $appointment;
    }

    public function asJob(int $appointmentId): void
    {
        $this->handle($appointmentId);
    }
}
