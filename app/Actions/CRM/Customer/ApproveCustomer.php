<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Jun 2024 22:24:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Models\CRM\Customer;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class ApproveCustomer extends OrgAction
{
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData): Customer
    {
        return $this->update($customer, $modelData);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::enum(CustomerStatusEnum::class)],
        ];
    }


    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($customer->organisation, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function action(Customer $customer, array $modelData): Customer
    {
        $this->initialisation($customer->organisation, $modelData);

        return $this->handle($customer, $this->validatedData);
    }
}
