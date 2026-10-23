<?php

namespace App\Actions\CRM\AppointmentStaff;

use App\Actions\OrgAction;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreAppointmentStaff extends OrgAction
{
    /**
     * @throws \Throwable
     */
    public function handle(Shop $shop, array $modelData): User
    {
        $user = User::findOrFail($modelData['user_id']);

        return UpdateAppointmentStaff::make()->handle($shop, $user, $modelData['meeting_modes']);
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
            'user_id'         => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('group_id', $this->shop->group_id)->where('status', true),
            ],
            'meeting_modes'   => ['required', 'array', 'min:1'],
            'meeting_modes.*' => [Rule::enum(AppointmentTypeMeetingModeEnum::class)],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Shop $shop, ActionRequest $request): User
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
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
    public function action(Shop $shop, array $modelData): User
    {
        $this->asAction = true;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }
}
