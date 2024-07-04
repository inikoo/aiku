<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:09:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateServices;
use App\Actions\OrgAction;
use App\Models\Catalogue\Service;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachServiceFromPalletReturn extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletReturn $palletReturn, Service $service, array $modelData = []): void
    {
        $palletReturn->services()->detach([$service->id]);

        PalletReturnHydrateServices::dispatch($palletReturn);
    }

    public function rules(): array
    {
        return [
            'quantity'   => ['sometimes', 'integer', 'min:1']
        ];
    }


    public function asController(PalletReturn $palletReturn, Service $service, ActionRequest $request): void
    {
        $this->initialisation($palletReturn->organisation, $request->all());

        $this->handle($palletReturn, $service, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, Service $service, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        $this->handle($palletReturn, $service, $this->validatedData);
    }
}
