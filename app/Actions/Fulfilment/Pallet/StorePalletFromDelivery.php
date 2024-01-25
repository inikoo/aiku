<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateStoredItems;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFulfilment;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StorePalletFromDelivery extends OrgAction
{
    private Customer $customer;

    public function handle(PalletDelivery $palletDelivery, array $modelData): Pallet
    {
        /** @var Pallet $pallet */
        $pallet = $palletDelivery->pallets()->create($modelData);
        //CustomerHydrateStoredItems::dispatch($customer);
        // OrganisationHydrateFulfilment::dispatch();

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Customer $customer, PalletDelivery $palletDelivery, ActionRequest $request): Pallet
    {
        $this->customer = $customer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($palletDelivery, $this->validateAttributes());
    }

    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->customer       = $customer;
        $this->initialisationFromFulfilment($customer->shop->fulfilment, $modelData);

        return $this->handle($customer, $this->validatedData);
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilment.pallets.show', $pallet->slug);
    }
}
