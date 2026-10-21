<?php

namespace App\Actions\CRM\Appointment;

use App\Actions\CRM\Appointment\GoogleCalendar\SyncAppointmentGoogleCalendarEvent;
use Illuminate\Support\Arr;
use App\Actions\CRM\Appointment\Zoom\SyncAppointmentZoomMeeting;
use App\Actions\OrgAction;
use App\Enums\CRM\Appointment\AppointmentSourceEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreAppointment extends OrgAction
{
    use WithAppointmentRules;
    use WithAppointmentNotification;

    private bool $notifyVisitor = true;

    /**
     * @throws \Throwable
     */
    public function handle(Shop $shop, array $modelData): Appointment
    {
        $appointment = DB::transaction(function () use ($shop, $modelData) {
            data_set($modelData, 'state', AppointmentStateEnum::ACCEPTED, overwrite: false);

            $modelData = $this->prepareAppointmentData($shop, $modelData);

            data_set($modelData, 'group_id', $shop->group_id);
            data_set($modelData, 'organisation_id', $shop->organisation_id);
            data_set($modelData, 'source', AppointmentSourceEnum::STAFF, overwrite: false);

            $this->ensureAppointmentFits($modelData);

            /** @var Appointment $appointment */
            $appointment = $shop->appointments()->create($modelData);

            if ($this->notifyVisitor) {
                $this->notifyVisitorAboutNewAppointment($appointment);
            }

            return $appointment;
        });

        SyncAppointmentStaffTask::run($appointment);
        SyncAppointmentGoogleCalendarEvent::dispatchFor($appointment);

        if ($appointment->appointmentType?->isZoomCall() || Arr::has($appointment->data, 'zoom')) {
            SyncAppointmentZoomMeeting::dispatch($appointment->id);
        }

        return $appointment;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            ...$this->appointmentRules(isUpdate: false),
            'source'             => ['sometimes', Rule::enum(AppointmentSourceEnum::class)],
            'created_by_user_id' => ['sometimes', 'nullable', 'integer'],
            'customer_id'        => ['sometimes', 'nullable', 'integer', Rule::exists('customers', 'id')->where('shop_id', $this->shop->id)],
            'marketing_opt_in'   => ['sometimes', 'boolean'],
            'state'              => ['sometimes', Rule::enum(AppointmentStateEnum::class)],
        ];
    }

    public function getValidationMessages(): array
    {
        return $this->appointmentPhoneMessages();
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->discardDialCodeOnlyPhone();

        if (!$this->asAction) {
            $this->set('created_by_user_id', $request->user()->id);
        }
    }

    public function asController(Shop $shop, ActionRequest $request): Appointment
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(Appointment $appointment): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.crm.appointments.index', [
            'organisation' => $appointment->organisation->slug,
            'shop'         => $appointment->shop->slug,
        ]);
    }

    /**
     * @param array<string, mixed> $data kept on the appointment as it is, e.g. where it was imported from
     */
    public function action(Shop $shop, array $modelData, bool $notifyVisitor = true, array $data = []): Appointment
    {
        $this->asAction      = true;
        $this->notifyVisitor = $notifyVisitor;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $data ? [...$this->validatedData, 'data' => $data] : $this->validatedData);
    }
}
