<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:09:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateServices;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachServiceFromPalletReturn extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletReturn $palletReturn, array $modelData): true
    {
        $palletReturn->services()->detach([$modelData['service_id']]);

        PalletReturnHydrateServices::dispatch($palletReturn);

        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'quantity'   => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): true
    {
        $this->initialisation($palletReturn->organisation, $request->all());

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): true
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }
}
