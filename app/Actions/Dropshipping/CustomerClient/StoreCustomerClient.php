<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:03:02 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\Dropshipping\CustomerClient\Hydrators\CustomerClientHydrateUniversalSearch;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateClients;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreCustomerClient
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(Customer $customer, array $modelData, array $addressesData = []): CustomerClient
    {
        $modelData['shop_id'] = $customer->shop_id;

        /** @var CustomerClient $customerClient */
        $customerClient = $customer->clients()->create($modelData);


        StoreAddressAttachToModel::run($customerClient, $addressesData, ['scope' => 'delivery']);
        CustomerClientHydrateUniversalSearch::dispatch($customerClient);
        CustomerHydrateClients::dispatch($customer);

        return $customerClient;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [

            'reference'                 => ['required', 'nullable', 'string', ],
            'contact_name'              => ['required', 'nullable', 'string', 'max:255'],
            'company_name'              => ['required', 'nullable', 'string', 'max:255'],
            'email'                     => ['required', 'nullable', 'email'],
            'phone'                     => ['required', 'nullable', 'string'],
        ];
    }

    public function action(Customer $customer, array $objectData, array $addressesData): CustomerClient
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($customer, $validatedData, $addressesData);
    }
}
