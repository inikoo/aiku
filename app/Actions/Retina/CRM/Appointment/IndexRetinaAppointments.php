<?php

namespace App\Actions\Retina\CRM\Appointment;

use App\Actions\CRM\AppointmentType\GetAppointmentTypeAvailableSlots;
use App\Actions\Retina\UI\Dashboard\ShowRetinaDashboard;
use App\Actions\RetinaAction;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentType;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexRetinaAppointments extends RetinaAction
{
    use WithRetinaAppointment;

    public const int PAST_APPOINTMENTS = 50;

    /**
     * @return array{upcoming: array<int, array<string, mixed>>, past: array<int, array<string, mixed>>, free_slots: array<int, array<string, array<int, string>>>}
     */
    public function handle(Customer $customer): array
    {
        $appointments = fn () => $customer->appointments()->with(['appointmentType', 'user', 'shop.timezone']);

        $upcoming = $appointments()
            ->whereIn('state', AppointmentStateEnum::holdingSlot())
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $past = $appointments()
            ->where(fn ($query) => $query
                ->whereNotIn('state', AppointmentStateEnum::holdingSlot())
                ->orWhere('ends_at', '<', now()))
            ->orderByDesc('starts_at')
            ->limit(self::PAST_APPOINTMENTS)
            ->get();

        return [
            'upcoming'   => $upcoming->map(fn (Appointment $appointment) => $this->customerAppointment($appointment))->all(),
            'past'       => $past->map(fn (Appointment $appointment) => $this->customerAppointment($appointment))->all(),
            'free_slots' => $upcoming
                ->filter(fn (Appointment $appointment) => $appointment->canBeChangedByVisitor())
                ->map(fn (Appointment $appointment) => $appointment->appointmentType)
                ->filter(fn (?AppointmentType $appointmentType) => $appointmentType && !$appointmentType->trashed() && $appointmentType->is_active)
                ->unique('id')
                ->mapWithKeys(fn (AppointmentType $appointmentType) => [$appointmentType->id => GetAppointmentTypeAvailableSlots::run($appointmentType)])
                ->all(),
        ];
    }

    /**
     * The menu entry shows once the shop takes bookings, or the customer already has one.
     *
     * @return array<string, mixed>|null
     */
    public static function navigation(WebUser $webUser): ?array
    {
        $customer = $webUser->customer;

        if (!$customer || !($customer->shop->appointmentTypes()->where('is_active', true)->exists() || $customer->appointments()->exists())) {
            return null;
        }

        return [
            'label' => __('Appointments'),
            'icon'  => ['fal', 'fa-calendar-check'],
            'root'  => 'retina.appointments.',
            'route' => [
                'name' => 'retina.appointments.index',
            ],
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $this->initialisation($request);

        return $this->handle($this->customer);
    }

    public function htmlResponse(array $appointments): Response
    {
        return Inertia::render(
            'Appointments/RetinaAppointments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Appointments'),
                'pageHead'    => [
                    'title' => __('Appointments'),
                    'icon'  => 'fal fa-calendar-check',
                ],
                'upcoming'    => $appointments['upcoming'],
                'past'        => $appointments['past'],
                'free_slots'  => (object) array_map(fn (array $slots) => (object) $slots, $appointments['free_slots']),
            ]
        );
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            ShowRetinaDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'retina.appointments.index',
                        ],
                        'label' => __('Appointments'),
                    ],
                ],
            ]
        );
    }
}
