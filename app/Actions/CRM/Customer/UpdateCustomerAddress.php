<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerAddress extends OrgAction
{
    public function handle(Customer $customer, array $modelData): Customer
    {
        $addressData = Arr::get($modelData, 'address');
        $countryCode = Country::find(Arr::get($modelData, 'country_id'))->code;
        data_set($addressData, 'country_code', $countryCode);
        $label = isset($modelData['label']) ? $modelData['label'] : null;
        unset($modelData['label']);
        unset($modelData['can_edit']);
        unset($modelData['can_delete']);
        $updatedAddress     = UpdateAddress::run(Address::find(Arr::get($addressData, 'id')), $addressData);
        $pivotData['label'] = $label;
        $customer->addresses()->updateExistingPivot(
            $updatedAddress->id,
            $pivotData
        );

        return $customer;
    }

    public function rules(): array
    {
        return [
            'address'             => ['sometimes'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if($this->scope instanceof Customer)
        {
            return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
        } else {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        }
        return false;
    }

    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $this->scope = $customer;
        $this->initialisationFromShop($customer->shop, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function fromFulfilmentCustomer(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Customer
    {
        $this->scope = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer->customer, $this->validatedData);
    }

    public function action(Customer $customer, $modelData): Customer
    {
        $this->asAction = true;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }
}
