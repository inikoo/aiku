<?php

namespace App\Actions\CRM\AppointmentStaff;

use App\Actions\OrgAction;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\AppointmentType;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateAppointmentStaff extends OrgAction
{
    /**
     * @throws \Throwable
     */
    public function handle(Shop $shop, User $user, array $meetingModes): User
    {
        DB::transaction(function () use ($shop, $user, $meetingModes) {
            $shop->appointmentTypes()->each(function (AppointmentType $appointmentType) use ($user, $meetingModes) {
                if (in_array($appointmentType->meeting_mode->value, $meetingModes, true)) {
                    $appointmentType->attendees()->syncWithoutDetaching([$user->id]);
                } else {
                    $appointmentType->attendees()->detach($user->id);
                }
            });
        });

        return $user;
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
            'meeting_modes'   => ['present', 'array'],
            'meeting_modes.*' => [Rule::enum(AppointmentTypeMeetingModeEnum::class)],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Shop $shop, User $user, ActionRequest $request): User
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $user, $this->validatedData['meeting_modes']);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.crm.appointments.staff.index', [
            'organisation' => $this->shop->organisation->slug,
            'shop'         => $this->shop->slug,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function action(Shop $shop, User $user, array $modelData): User
    {
        $this->asAction = true;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $user, $this->validatedData['meeting_modes']);
    }
}
