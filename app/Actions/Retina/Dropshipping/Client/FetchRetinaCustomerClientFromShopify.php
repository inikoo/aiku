<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 16 Oct 2024 10:47:26 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Dropshipping\Client;

use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\Retina\Dropshipping\Client\Traits\WithGeneratedShopifyAddress;
use App\Actions\RetinaAction;
use App\Models\CRM\Customer;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class FetchRetinaCustomerClientFromShopify extends RetinaAction
{
    use WithGeneratedShopifyAddress;

    private Customer $parent;

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $shopifyUser = $this->parent->shopifyUser;

        $response = $shopifyUser->api()->getRestClient()->request('GET', 'admin/api/2024-07/customers.json');

        if (!$response['errors']) {
            foreach ($response['body']['customers'] as $customer) {
                $address = Arr::get($customer, 'addresses')[0];
                $existsClient = $this->parent->clients()->where('email', $customer['email'])->first();

                $attributes = $this->getAttributes($customer['container'], $address['container']);

                if (!$existsClient) {
                    StoreCustomerClient::make()->action($this->parent, $attributes);
                } else {
                    UpdateCustomerClient::run($existsClient, $attributes);
                }
            }
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): void
    {
        $this->initialisation($request);
        $this->parent = $this->customer;

        $this->handle();
    }
}
