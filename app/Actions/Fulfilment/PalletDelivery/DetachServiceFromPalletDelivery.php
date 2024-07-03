<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateServices;
use App\Actions\OrgAction;
use App\Models\Catalogue\Service;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachServiceFromPalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletDelivery $palletDelivery, Service $service, array $modelData = []): void
    {
        $palletDelivery->services()->detach([$service->id]);

        PalletDeliveryHydrateServices::dispatch($palletDelivery);
    }

    public function rules(): array
    {
        return [
            'quantity'   => ['sometimes', 'integer', 'min:1']
        ];
    }

    public function asController(PalletDelivery $palletDelivery, Service $service, ActionRequest $request): void
    {
        $this->initialisation($palletDelivery->organisation, $request->all());

        $this->handle($palletDelivery, $service, $this->validatedData);
    }

    public function fromRetina(PalletDelivery $palletDelivery, Service $service, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $this->handle($palletDelivery, $service, $this->validatedData);
    }
}
