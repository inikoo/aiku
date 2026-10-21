<?php

namespace App\Actions\CRM\Appointment\UI;

use App\Actions\CRM\AppointmentType\GetAppointmentTypeAvailableSlots;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\CRM\AppointmentType;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait WithAppointmentForm
{
    protected function appointmentWhenFields(Shop $shop, ?Appointment $appointment = null): array
    {
        $timezone = $shop->timezone?->name ?? 'UTC';

        $appointmentTypeIdsByUser = DB::table('appointment_type_user')
            ->join('appointment_types', 'appointment_types.id', '=', 'appointment_type_user.appointment_type_id')
            ->where('appointment_types.shop_id', $shop->id)
            ->whereNull('appointment_types.deleted_at')
            ->get(['appointment_type_user.user_id', 'appointment_type_user.appointment_type_id'])
            ->groupBy('user_id')
            ->map(fn (Collection $rows) => $rows->pluck('appointment_type_id')->all());

        return [
            'appointment_type_id' => [
                'type'      => 'select',
                'label'     => __('Type'),
                'required'  => true,
                'labelProp' => 'name',
                'valueProp' => 'id',
                'options'   => $shop->appointmentTypes()->orderBy('name')->get(['id', 'name'])->toArray(),
                'value'     => $appointment?->appointment_type_id,
            ],
            'starts_at'           => [
                'type'        => 'appointment_slot',
                'label'       => __('Date and time'),
                'required'    => true,
                'full'        => true,
                'information' => __('Only the free times of the chosen type are offered, the same as on the website. In the shop\'s timezone (:timezone).', ['timezone' => $timezone]),
                'timezone'            => $timezone,
                'appointment_type_id' => $appointment?->appointment_type_id,
                'slots'       => (object) $shop->appointmentTypes()
                    ->get()
                    ->mapWithKeys(fn (AppointmentType $appointmentType) => [$appointmentType->id => (object) GetAppointmentTypeAvailableSlots::run($appointmentType)])
                    ->all(),
                'value'       => $appointment?->starts_at->copy()->setTimezone($timezone)->format('Y-m-d H:i'),
            ],
            'user_id'             => [
                'type'                => 'appointment_staff',
                'label'               => __('Staff'),
                'information'         => __('Only staff who arrange the chosen type are listed. Leave empty and any of them can take it.'),
                'appointment_type_id' => $appointment?->appointment_type_id,
                'options'             => User::whereIn('id', $appointmentTypeIdsByUser->keys())
                    ->orderBy('contact_name')
                    ->get(['id', 'contact_name', 'username', 'nickname'])
                    ->map(fn (User $user) => [
                        'id'                   => $user->id,
                        'name'                 => $user->chatName(),
                        'appointment_type_ids' => $appointmentTypeIdsByUser->get($user->id),
                    ])
                    ->values()
                    ->all(),
                'value'               => $appointment?->user_id,
            ],
        ];
    }

    protected function appointmentVisitorFields(Shop $shop, ?Appointment $appointment = null): array
    {
        $videoCallTypes = $shop->appointmentTypes()
            ->where('meeting_mode', AppointmentTypeMeetingModeEnum::VIDEO_CALL)
            ->get();

        return [
            'contact_name'    => [
                'type'     => 'input',
                'label'    => __('Name'),
                'required' => true,
                'value'    => $appointment?->contact_name ?? '',
            ],
            'email'           => [
                'type'        => 'input',
                'label'       => __('Email'),
                'information' => __('Required for video calls. Linked to the customer or prospect with this email or phone. Someone new is added as a prospect who gets no marketing emails.'),
                'requiredIf'  => ['field' => 'appointment_type_id', 'values' => $videoCallTypes->pluck('id')->values()->all()],
                'value'       => $appointment?->email ?? '',
            ],
            'phone'           => [
                'type'        => 'phone',
                'label'       => __('Phone'),
                'information' => __('Required for video calls on WhatsApp.'),
                'requiredIf'  => ['field' => 'appointment_type_id', 'values' => $videoCallTypes->filter(fn (AppointmentType $appointmentType) => $appointmentType->requiresPhone())->pluck('id')->values()->all()],
                'value'       => $appointment?->phone ?? '',
                'options'     => [
                    'defaultCountry' => $shop->country?->code,
                ],
            ],
            'number_visitors' => [
                'type'  => 'input_number',
                'label' => __('People coming'),
                'bind'  => ['min' => 1, 'max' => 100],
                'value' => $appointment?->number_visitors ?? 1,
            ],
            'notes'           => [
                'type'  => 'textarea',
                'label' => __('Notes'),
                'value' => $appointment?->notes ?? '',
            ],
        ];
    }

    protected function appointmentStateField(Appointment $appointment): array
    {
        return [
            'type'     => 'select',
            'label'    => __('Status'),
            'required' => true,
            'options'  => AppointmentStateEnum::valuesWithLabels(),
            'value'    => $appointment->state->value,
        ];
    }
}
