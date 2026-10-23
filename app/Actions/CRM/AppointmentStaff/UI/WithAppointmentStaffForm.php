<?php

namespace App\Actions\CRM\AppointmentStaff\UI;

use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\User;

trait WithAppointmentStaffForm
{
    protected function meetingModesField(Shop $shop, ?User $user = null): array
    {
        return [
            'type'        => 'select',
            'mode'        => 'multiple',
            'label'       => __('Appointments they arrange'),
            'information' => __('They arrange every appointment type of the chosen kind, including types added later, and are told about each booking.'),
            'required'    => true,
            'labelProp'   => 'name',
            'valueProp'   => 'id',
            'options'     => array_map(
                fn (AppointmentTypeMeetingModeEnum $meetingMode) => ['id' => $meetingMode->value, 'name' => $meetingMode->label()],
                AppointmentTypeMeetingModeEnum::cases()
            ),
            'value'       => $user
                ? $shop->appointmentTypes()
                    ->whereHas('attendees', fn ($query) => $query->where('users.id', $user->id))
                    ->distinct()
                    ->orderBy('meeting_mode')
                    ->pluck('meeting_mode')
                    ->map(fn (AppointmentTypeMeetingModeEnum $meetingMode) => $meetingMode->value)
                    ->all()
                : [],
        ];
    }
}
