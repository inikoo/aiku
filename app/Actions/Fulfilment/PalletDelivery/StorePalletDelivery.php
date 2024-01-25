<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateStoredItems;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFulfilment;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Pallet;
use App\Models\PalletDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePalletDelivery
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(Customer $customer, array $modelData): PalletDelivery
    {
        /** @var \App\Models\PalletDelivery $palletDelivery */
        $palletDelivery = $customer->palletDeliveries()->create($modelData);

        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.edit");
    }


    public function rules(): array
    {
        return [
            'reference'   => ['required', 'unique:pallets', 'between:2,9', 'alpha'],
            'location_id' => ['required', 'exists:locations,id']
        ];
    }

    public function asController(Customer $customer, ActionRequest $request): PalletDelivery
    {
        $this->customer = $customer;
        $this->setRawAttributes($request->all());

        return $this->handle($customer, $this->validateAttributes());
    }

    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.fulfilment.pallets.show', $pallet->slug);
    }
}
