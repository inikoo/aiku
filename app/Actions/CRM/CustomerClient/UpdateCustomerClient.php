<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 11:21:47 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerClient;

use App\Actions\CRM\CustomerClient\Search\CustomerClientRecordSearch;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\CRM\CustomerClientResource;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\SysAdmin\Organisation;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerClient extends OrgAction
{
    use WithActionUpdate;

    public function handle(CustomerClient $customerClient, array $modelData): CustomerClient
    {
        if (Arr::has($modelData, 'address')) {
            $AddressData = Arr::get($modelData, 'address');
            Arr::forget($modelData, 'address');

            UpdateAddress::run($customerClient->address, $AddressData);
            $customerClient->updateQuietly(
                [
                    'location' => $customerClient->address->getLocation()
                ]
            );
        }

        $customerClient = $this->update($customerClient, $modelData, ['data']);
        CustomerClientRecordSearch::dispatch($customerClient);

        return $customerClient;
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
            'reference'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'status'         => ['sometimes', 'boolean'],
            'contact_name'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'          => ['sometimes', 'nullable', 'email'],
            'phone'          => ['sometimes', 'nullable', new Phone()],
            'address'        => ['sometimes', new ValidAddress()],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
        ];

        if (!$this->strict) {
            $rules['phone']           = ['sometimes', 'nullable', 'string', 'max:255'];
            $rules['email']           = ['sometimes', 'nullable', 'string', 'max:255'];
            $rules['source_id']       = ['sometimes', 'nullable', 'string', 'max:255'];
            $rules['created_at']      = ['sometimes', 'date'];
            $rules['last_fetched_at'] = ['sometimes', 'date'];
            $rules['deleted_at']      = ['sometimes', 'nullable', 'date'];
        }

        return $rules;
    }

    public function asController(Organisation $organisation, Shop $shop, CustomerClient $customerClient, ActionRequest $request): CustomerClient
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customerClient, $this->validatedData);
    }

    public function action(CustomerClient $customerClient, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): CustomerClient
    {
        $this->strict = $strict;
        if (!$audit) {
            CustomerClient::disableAuditing();
        }
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;
        $this->initialisationFromShop($customerClient->shop, $modelData);

        return $this->handle($customerClient, $this->validatedData);
    }


    public function jsonResponse(CustomerClient $customerClient): CustomerClientResource
    {
        return new CustomerClientResource($customerClient);
    }
}
