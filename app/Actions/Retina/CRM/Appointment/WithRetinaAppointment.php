<?php

namespace App\Actions\Retina\CRM\Appointment;

use App\Models\CRM\Appointment;
use App\Models\CRM\Customer;
use Illuminate\Validation\ValidationException;

trait WithRetinaAppointment
{
    protected function ensureAppointmentOfCustomer(Appointment $appointment, ?Customer $customer): void
    {
        abort_unless($customer && $appointment->visitor_type === $customer->getMorphClass() && $appointment->visitor_id === $customer->id, 404);
    }

    protected function ensureCustomerCanChange(Appointment $appointment): void
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
    protected function customerAppointment(Appointment $appointment): array
    {
        $timezone        = $appointment->shop->timezone?->name ?? 'UTC';
        $startsAt        = $appointment->starts_at->copy()->setTimezone($timezone);
        $appointmentType = $appointment->appointmentType;

        return [
            'id'                  => $appointment->id,
            'appointment_type_id' => $appointment->appointment_type_id,
            'appointment_type'    => $appointmentType?->name,
            'meeting_mode'        => $appointmentType?->meeting_mode->value,
            'location'            => $appointmentType?->location,
            'date_label'          => $startsAt->translatedFormat('l j F Y'),
            'time'                => $startsAt->format('H:i'),
            'ends_time'           => $appointment->ends_at->copy()->setTimezone($timezone)->format('H:i'),
            'timezone'            => $timezone,
            'state'               => $appointment->state->value,
            'state_label'         => $appointment->state->label(),
            'staff_name'          => $appointment->user?->contact_name,
            'can_change'          => $appointment->canBeChangedByVisitor(),
        ];
    }
}
