<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): PalletDelivery
    {
        if (!Arr::get($modelData, 'reference')) {
            data_set($modelData, 'reference', GetSerialReference::run(container: $fulfilmentCustomer->customer->shop, modelType: SerialReferenceModelEnum::PALLET_DELIVERY));
        }

        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $fulfilmentCustomer->palletDeliveries()->create($modelData);


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

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Customer $customer, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.fulfilment.pallets.show', $pallet->slug);
    }
}
