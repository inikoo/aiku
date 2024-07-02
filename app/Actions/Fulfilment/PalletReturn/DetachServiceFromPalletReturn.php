<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:09:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateServices;
use App\Actions\OrgAction;
use App\Enums\UI\Fulfilment\PalletReturnTabsEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachServiceFromPalletReturn extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $palletReturn->services()->detach([$modelData['service_id']]);

        PalletReturnHydrateServices::dispatch($palletReturn);

        return $palletReturn;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'quantity'   => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisation($palletReturn->organisation, $request->all());

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): RedirectResponse
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.pallet-return.service.store' => Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet_returns.show', [
                'organisation'           => $palletReturn->organisation->slug,
                'fulfilment'             => $palletReturn->fulfilment->slug,
                'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
                'palletReturn'           => $palletReturn->slug,
                'tab'                    => PalletReturnTabsEnum::SERVICES->value
            ]),
            default => Redirect::route('retina.storage.pallet-return.show', [
                'palletReturn'     => $palletReturn->slug
            ])
        };
    }
}