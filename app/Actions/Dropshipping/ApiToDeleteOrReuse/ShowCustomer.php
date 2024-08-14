<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 13:36:08 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\ApiToDeleteOrReuse;

use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\CustomersResource;
use App\Models\CRM\Customer;
use Lorisleiva\Actions\ActionRequest;

class ShowCustomer extends GrpAction
{
    private Customer $customer;

    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $group         = $request->user();
        $this->customer=$customer;
        $this->initialisation($group, $request);

        return $this->handle($customer);
    }

    public function handle(Customer $customer): Customer
    {
        return $customer;
    }

    public function prepareForValidation(): void
    {
        if($this->customer->shop->type!=ShopTypeEnum::DROPSHIPPING) {
            abort(404);
        }

    }


    public function jsonResponse($customer): CustomersResource
    {
        return CustomersResource::make($customer);
    }

}
