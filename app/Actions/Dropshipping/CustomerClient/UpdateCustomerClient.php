<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:03:02 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\Dropshipping\CustomerClient\Hydrators\CustomerClientHydrateUniversalSearch;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Dropshipping\CustomerClientResource;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
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
            UpdateAddress::run($customerClient->address, $AddressData);
            $customerClient->updateQuietly(
                [
                    'location' => $customerClient->address->getLocation()
                ]
            );
        }

        $customerClient = $this->update($customerClient, $modelData, ['data']);
        CustomerClientHydrateUniversalSearch::dispatch($customerClient);

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
            'reference'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_name'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone'            => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'            => ['sometimes', 'nullable', 'string', 'max:255'],
            'address'          => ['sometimes', new ValidAddress()],
            'source_id'        => 'sometimes|nullable|string|max:255',
            'status'           => ['sometimes', 'boolean'],
        ];

        if ($this->strict) {
            $strictRules = [
                'phone' => ['sometimes', 'nullable', 'phone:AUTO'],
                'email' => [
                    'nullable',
                    'email',
                ],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
    }

    public function asController(Organisation $organisation, Shop $shop, CustomerClient $customerClient, ActionRequest $request): CustomerClient
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customerClient, $this->validatedData);
    }

    public function action(CustomerClient $customerClient, $modelData): CustomerClient
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $this->initialisationFromShop($customerClient->shop, $modelData);

        return $this->handle($customerClient, $this->validatedData);
    }

    public function asFetch(CustomerClient $customerClient, $modelData): CustomerClient
    {
        $this->asAction       = true;
        $this->strict         = false;
        $this->hydratorsDelay = 60;
        $this->setRawAttributes($modelData);
        $this->initialisationFromShop($customerClient->shop, $modelData);

        return $this->handle($customerClient, $this->validatedData);
    }

    public function jsonResponse(CustomerClient $customerClient): CustomerClientResource
    {
        return new CustomerClientResource($customerClient);
    }
}
