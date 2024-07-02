<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Jun 2024 22:24:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\Ordering\Platform;

class UpdateCustomerPlatform extends OrgAction
{
    use WithActionUpdate;

    public function handle(Customer $customer, Platform $platform, array $modelData): Customer
    {
        $currentPlatform = $customer->platform();
        $customer->platforms()->detach($currentPlatform->id);
        $customer=AttachCustomerToPlatform::make()->action($customer, $platform, $modelData);

        foreach ($customer->portfolios as $dropshippingCustomerPortfolio) {
            $customer->platforms()->detach($dropshippingCustomerPortfolio->platform());
        }

        return $customer;

    }

    public function rules(): array
    {
        return [
            'reference' => 'nullable|string|max:255',
        ];
    }

    public function action(Customer $customer, Platform $platform, array $modelData): Customer
    {
        $this->initialisation($customer->organisation, $modelData);
        return $this->handle($customer, $platform, $this->validatedData);
    }
}
