<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\PalletReturn;

use App\Actions\Fulfilment\PalletReturn\Search\PalletReturnRecordSearch;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class RetinaUpdatePalletReturn extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;
    use WithModelAddressActions;

    public Customer $customer;
    /**
     * @var true
     */
    private bool $action = false;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        if (Arr::exists($modelData, 'address')) {
            $addressData = Arr::get($modelData, 'address');
            $groupId     = $palletReturn->group_id;

            data_set($addressData, 'group_id', $groupId);

            if (Arr::exists($addressData, 'id')) {
                $countryCode = Country::find(Arr::get($addressData, 'country_id'))->code;
                data_set($addressData, 'country_code', $countryCode);
                $label = isset($addressData['label']) ? $addressData['label'] : null;
                unset($addressData['label']);
                unset($addressData['can_edit']);
                unset($addressData['can_delete']);
                $updatedAddress     = UpdateAddress::run(Address::find(Arr::get($addressData, 'id')), $addressData);
                $pivotData['label'] = $label;
                $palletReturn->fulfilmentCustomer->customer->addresses()->updateExistingPivot(
                    $updatedAddress->id,
                    $pivotData
                );
            } else {
                $this->addAddressToModelFromArray(
                    $palletReturn->fulfilmentCustomer->customer,
                    $addressData,
                    'delivery',
                    false,
                    'delivery_address_id'
                );
            }

            Arr::forget($modelData, 'address');
        }

        $palletReturn = $this->update($palletReturn, $modelData);
        PalletReturnRecordSearch::dispatch($palletReturn);

        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference'      => ['sometimes', 'string', 'max:255'],
            'public_notes'   => ['sometimes', 'nullable', 'string', 'max:4000'],
            'internal_notes' => ['sometimes', 'nullable', 'string', 'max:4000'],
        ];
    }

    public function asController(Organisation $organisation, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->initialisation($request);

        return $this->handle($palletReturn, $this->validatedData);
    }
}
