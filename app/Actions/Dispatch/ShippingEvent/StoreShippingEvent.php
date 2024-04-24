<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\ShippingEvent;

use App\Actions\OrgAction;
use App\Models\Dispatch\ShippingEvent;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShippingEvent extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): ShippingEvent
    {
        data_set($modelData, 'organisation_id', $this->organisation->id);
        data_set($modelData, 'sent_at', now());

        return ShippingEvent::create($modelData);
    }

    public function rules(): array
    {
        return [
            'events' => ['required',  'array']
        ];
    }

    public function action(Organisation $organisation, array $modelData): ShippingEvent
    {
        $this->initialisation($organisation, $modelData);

        return $this->handle($this->validatedData);
    }
}
