<?php

namespace App\Actions\Iris\Appointment;

use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use Illuminate\Validation\ValidationException;

trait WithIrisAppointmentAccess
{
    /**
     * The link in the visitor's emails is the only key to their appointment, and it opens it on
     * the website it was booked on alone.
     */
    protected function ensureVisitorLink(Appointment $appointment, string $token): void
    {
        abort_unless($appointment->shop_id === $this->shop->id && hash_equals($appointment->manageToken(), $token), 404);
    }

    protected function ensureVisitorCanChange(Appointment $appointment): void
    {
        if (!$appointment->canBeChangedByVisitor()) {
            throw ValidationException::withMessages([
                'state' => __('This appointment can no longer be changed online. Please contact us.'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function visitorAppointment(Appointment $appointment): array
    {
        $timezone        = $appointment->shop->timezone?->name ?? 'UTC';
        $startsAt        = $appointment->starts_at->copy()->setTimezone($timezone);
        $appointmentType = $appointment->appointmentType;

        return [
            'id'                 => $appointment->id,
            'token'              => $appointment->manageToken(),
            'appointment_type'   => $appointmentType?->name,
            'meeting_mode'       => $appointmentType?->meeting_mode->value,
            'meeting_mode_label' => $appointmentType?->meeting_mode->label(),
            'media'              => $appointmentType?->media()?->value,
            'zoom_join_url'      => $appointment->state === AppointmentStateEnum::ACCEPTED ? $appointment->zoomJoinUrl() : null,
            'google_meet_join_url' => $appointment->state === AppointmentStateEnum::ACCEPTED ? $appointment->googleMeetJoinUrl() : null,
            'location'           => $appointmentType?->location,
            'duration_minutes'   => $appointmentType?->duration_minutes,
            'date'               => $startsAt->toDateString(),
            'date_label'         => $startsAt->translatedFormat('l j F Y'),
            'time'               => $startsAt->format('H:i'),
            'ends_time'          => $appointment->ends_at->copy()->setTimezone($timezone)->format('H:i'),
            'timezone'           => $timezone,
            'state'              => $appointment->state->value,
            'state_label'        => $appointment->state->label(),
            'state_reason'       => $appointment->state_reason,
            'staff_name'         => $appointment->user?->contact_name,
            'number_visitors'    => $appointment->number_visitors,
            'can_change'         => $appointment->canBeChangedByVisitor(),
        ];
    }
}
