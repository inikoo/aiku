<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateServices;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncServiceToPalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletDelivery $palletDelivery, array $modelData): void
    {
        $palletDelivery->services()->syncWithoutDetaching([
            $modelData['service_id'] => ['quantity' => $modelData['quantity']]
        ]);

        PalletDeliveryHydrateServices::run($palletDelivery);
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'quantity'   => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->initialisation($palletDelivery->organisation, $request->all());

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function fromRetina(PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $this->handle($palletDelivery, $this->validatedData);
    }
}
