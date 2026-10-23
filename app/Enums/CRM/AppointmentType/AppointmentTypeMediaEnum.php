<?php

namespace App\Enums\CRM\AppointmentType;

use App\Enums\EnumHelperTrait;

enum AppointmentTypeMediaEnum: string
{
    use EnumHelperTrait;

    case WHATSAPP    = 'whatsapp';
    case ZOOM        = 'zoom';
    case GOOGLE_MEET = 'google_meet';

    public function label(): string
    {
        return match ($this) {
            self::WHATSAPP    => 'WhatsApp',
            self::ZOOM        => 'Zoom',
            self::GOOGLE_MEET => 'Google Meet',
        };
    }
}
