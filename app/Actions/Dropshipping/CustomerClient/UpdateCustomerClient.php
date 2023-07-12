<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:03:02 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\Dropshipping\CustomerClient\Hydrators\CustomerClientHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Dropshipping\CustomerClientResource;
use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerClient
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(CustomerClient $customerClient, array $modelData): CustomerClient
    {
        $customerClient = $this->update($customerClient, $modelData, ['data']);
        CustomerClientHydrateUniversalSearch::dispatch($customerClient);

        return $customerClient;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'reference'    => ['sometimes', 'string'],
            'contact_name' => ['sometimes', 'string'],
            'company_name' => ['sometimes', 'string'],
            'phone'        => ['sometimes', 'nullable', 'phone:AUTO'],
            'email'        => ['sometimes', 'nullable', 'email'],
        ];
    }

    public function asController(CustomerClient $customerClient, ActionRequest $request): CustomerClient
    {
        $request->validate();

        return $this->handle($customerClient, $request->all());
    }

    public function action(CustomerClient $customerClient, $objectData): CustomerClient
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($customerClient, $validatedData);
    }

    public function jsonResponse(CustomerClient $customerClient): CustomerClientResource
    {
        return new CustomerClientResource($customerClient);
    }
}
