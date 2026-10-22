<?php

namespace App\Actions\CRM\Appointment\Widget;

use App\Actions\Iris\Appointment\StoreIrisAppointment;
use App\Models\CRM\Appointment;
use Lorisleiva\Actions\ActionRequest;

class StoreAppointmentWidgetBooking extends StoreIrisAppointment
{
    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): Appointment
    {
        $key  = $request->query('key');
        $shop = AppointmentWidgetKey::make()->resolveShop(is_string($key) ? $key : null);

        if (!$shop) {
            abort(404, 'Unknown appointment widget');
        }

        $this->shop         = $shop;
        $this->organisation = $shop->organisation;
        $this->group        = $shop->group;

        $this->fillFromRequest($request);
        $this->validatedData = $this->validateAttributes();

        return $this->handle($shop, $this->validatedData);
    }
}
