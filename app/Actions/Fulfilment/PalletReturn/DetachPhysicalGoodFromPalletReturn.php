<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:10:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePhysicalGoods;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachPhysicalGoodFromPalletReturn extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletReturn $palletReturn, array $modelData): void
    {
        $palletReturn->physicalGoods()->detach([$modelData['outer_id']]);

        PalletReturnHydratePhysicalGoods::dispatch($palletReturn);
    }

    public function rules(): array
    {
        return [
            'outer_id'   => ['required', 'integer', Rule::exists('products', 'id')],
            'quantity'   => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisation($palletReturn->organisation, $request->all());

        $this->handle($palletReturn, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        $this->handle($palletReturn, $this->validatedData);
    }
}
