<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class StorePalletFromDelivery extends OrgAction
{
    use AsCommand;

    private Customer $customer;

    public $commandSignature = 'pallet:store-from-delivery {palletDelivery}';

    public function handle(PalletDelivery $palletDelivery, array $modelData): Pallet
    {
        /** @var Pallet $pallet */
        $pallet = $palletDelivery->pallets()->create($modelData);
        //FulfilmentCustomerHydrateStoredItems::dispatch($customer);
        // OrganisationHydrateFulfilmentCustomers::dispatch();

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


    public function asCommand(Command $command): int
    {
        $palletDelivery = PalletDelivery::where('reference', $command->argument('palletDelivery'))->firstOrFail();

        $this->handle($palletDelivery, [
            'group_id'               => $palletDelivery->group_id,
            'organisation_id'        => $palletDelivery->organisation_id,
            'fulfilment_id'          => $palletDelivery->fulfilment_id,
            'fulfilment_customer_id' => $palletDelivery->fulfilment_customer_id,
            'warehouse_id'           => $palletDelivery->warehouse_id,
            'slug'                   => now()->timestamp
        ]);

        echo "Pallet created from delivery: {$palletDelivery->reference}\n";

        return 0;
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.pallets.show', $pallet->slug);
    }
}
