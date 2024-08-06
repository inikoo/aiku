<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerDeliveryAddress extends OrgAction
{
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData): Customer
    {
        if (isset($modelData['delivery_address_id'])) {
            $customer->delivery_address_id = $modelData['delivery_address_id'];
            $customer->save();
        }
        return $customer;
    }

    public function rules(): array
    {
        $rules = [
            'delivery_address_id'         => ['sometimes', 'nullable', 'exists:addresses,id'],
        ];

        return $rules;
    }

    public function asController(Organisation $organisation, Customer $customer, ActionRequest $request): Customer
    {
        $this->customer = $customer;
        $this->initialisationFromShop($customer->shop, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function fromRetina(Customer $customer, ActionRequest $request): Customer
    {
        $customer = $request->user()->customer;

        $this->initialisation($request->get('website')->organisation, $request);

        return $this->handle($customer, $this->validatedData);
    }
}
