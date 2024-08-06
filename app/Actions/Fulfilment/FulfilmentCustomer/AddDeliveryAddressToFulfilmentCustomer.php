<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\AddDeliveryAddressToCustomer;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Helpers\TaxNumber\DeleteTaxNumber;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\Helpers\TaxNumber\UpdateTaxNumber;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class AddDeliveryAddressToFulfilmentCustomer extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): FulfilmentCustomer
    {



        $customer=AddDeliveryAddressToCustomer::make()->action($fulfilmentCustomer->customer,$modelData);




        FulfilmentCustomerHydrateUniversalSearch::dispatch($customer)->delay($this->hydratorsDelay);

        return $customer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'delivery_address'         => ['required', new ValidAddress()],
        ];

        return $rules;
    }


    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Customer
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Customer
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);


        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function jsonResponse(FulfilmentCustomer $fulfilmentCustomer): FulfilmentCustomerResource
    {
        return new FulfilmentCustomerResource($fulfilmentCustomer);
    }
}
