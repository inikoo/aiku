<?php

namespace App\Enums\CRM\Appointment;

use App\Enums\EnumHelperTrait;

enum AppointmentStaffNoticeEnum: string
{
    use EnumHelperTrait;

    case BOOKED      = 'booked';
    case ASSIGNED    = 'assigned';
    case RESCHEDULED = 'rescheduled';
    case CANCELLED   = 'cancelled';

    public function subject(string $contactName, string $date): string
    {
        return match ($this) {
            self::BOOKED      => __('New appointment: :name on :date', ['name' => $contactName, 'date' => $date]),
            self::ASSIGNED    => __('Appointment given to you: :name on :date', ['name' => $contactName, 'date' => $date]),
            self::RESCHEDULED => __('Appointment moved: :name on :date', ['name' => $contactName, 'date' => $date]),
            self::CANCELLED   => __('Appointment cancelled: :name on :date', ['name' => $contactName, 'date' => $date]),
        };
    }
}
