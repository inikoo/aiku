<?php

namespace App\Http\Resources\CRM;

use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $username
 * @property string|null $contact_name
 * @property string|null $meeting_modes
 */
class AppointmentStaffResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'contact_name'  => $this->contact_name ?: $this->username,
            'meeting_modes' => collect(explode(',', (string) $this->meeting_modes))
                ->map(fn (string $meetingMode) => AppointmentTypeMeetingModeEnum::tryFrom($meetingMode)?->label())
                ->filter()
                ->implode(', '),
        ];
    }
}
