<?php

namespace App\Actions\CRM\Appointment\GoogleCalendar;

use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\CRM\Appointment;
use App\Services\GoogleCalendar\GoogleCalendarClient;
use Illuminate\Http\Client\RequestException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The event follows the appointment: it is written when the appointment is booked, moved or
 * changed, and removed when it is declined or cancelled. A request still waiting for an answer is
 * a tentative event.
 */
class SyncAppointmentGoogleCalendarEvent
{
    use AsAction;

    public string $jobQueue = 'low-priority';

    public int $jobTries = 3;

    public static function dispatchFor(Appointment $appointment): void
    {
        if (GoogleCalendarClient::isConnected($appointment->shop)) {
            static::dispatch($appointment->id);
        }
    }

    /**
     * @throws RequestException
     */
    public function handle(int $appointmentId): void
    {
        $appointment = Appointment::withTrashed()->with(['shop', 'appointmentType', 'user'])->find($appointmentId);
        $client      = $appointment ? GoogleCalendarClient::forShop($appointment->shop) : null;

        if (!$client) {
            return;
        }

        if ($appointment->trashed() || in_array($appointment->state, AppointmentStateEnum::closed(), true)) {
            $client->deleteEvent($appointment->id);

            return;
        }

        $client->saveEvent($appointment->id, $this->event($appointment));
    }

    public function event(Appointment $appointment): array
    {
        $timezone = $appointment->shop->timezone?->name ?? 'UTC';
        $type     = $appointment->appointmentType;

        $description = array_filter([
            $appointment->state->label(),
            $appointment->email ? __('Email').': '.$appointment->email : null,
            $appointment->phone ? __('Phone').': '.$appointment->phone : null,
            $type?->meeting_mode === AppointmentTypeMeetingModeEnum::STORE_VISIT ? __('People coming').': '.$appointment->number_visitors : null,
            $appointment->user ? __('With').': '.$appointment->user->contact_name : null,
            $appointment->notes,
            route('grp.org.shops.show.crm.appointments.show', [$appointment->organisation->slug, $appointment->shop->slug, $appointment->id]),
        ]);

        return [
            'summary'            => $appointment->contact_name.($type ? ' · '.$type->name : ''),
            'description'        => implode("\n", $description),
            'location'           => $type?->meeting_mode === AppointmentTypeMeetingModeEnum::VIDEO_CALL ? __('Video call on WhatsApp') : $type?->location,
            'status'             => $appointment->state === AppointmentStateEnum::REQUESTED ? 'tentative' : 'confirmed',
            'start'              => ['dateTime' => $appointment->starts_at->copy()->setTimezone($timezone)->toIso8601String(), 'timeZone' => $timezone],
            'end'                => ['dateTime' => $appointment->ends_at->copy()->setTimezone($timezone)->toIso8601String(), 'timeZone' => $timezone],
            'extendedProperties' => ['private' => [GoogleCalendarClient::EVENT_PROPERTY => (string) $appointment->id]],
        ];
    }
}
