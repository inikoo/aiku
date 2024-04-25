<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\ShippingEvent;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dispatch\ShippingEvent;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateShippingEvent extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShippingEvent $shippingEvent, array $modelData): ShippingEvent
    {
        return $this->update($shippingEvent, $modelData, ['events', 'data']);
    }

    public function rules(): array
    {
        return [
            'events' => ['required',  'array']
        ];
    }

    public function action(ShippingEvent $shippingEvent, array $modelData): ShippingEvent
    {
        $this->initialisation($shippingEvent->organisation, $modelData);

        return $this->handle($shippingEvent, $this->validatedData);
    }
}
