<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 16 Oct 2024 10:47:26 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Dropshipping\Client;

use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\RetinaAction;
use App\Models\CRM\Customer;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class FetchRetinaCustomerClientFromShopify extends RetinaAction
{
    private Customer $parent;

    public function handle(ActionRequest $request): void
    {
        $shopifyUser = $this->parent->shopifyUser;

        $response = $shopifyUser->api()->getRestClient()->request('GET', 'admin/api/2024-07/customers.json');

        if (!$response['errors']) {
            foreach ($response['body']['customers'] as $customer) {
                $address = Arr::get($customer, 'addresses')[0];

                $country = Country::where('code', Arr::get($address, 'country_code'))->first();

                $existsClient = $this->parent->clients()->where('email', $customer['email'])->first();

                $attributes = [
                    'contact_name' => $customer['first_name'] . ' ' . Arr::get($customer, 'last_name'),
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'address' => [
                        'address_line_1'      => Arr::get($address, 'address1'),
                        'address_line_2'      => Arr::get($address, 'address2'),
                        'sorting_code'        => null,
                        'postal_code'         => Arr::get($address, 'zip'),
                        'dependent_locality'  => null,
                        'locality'            => Arr::get($address, 'city'),
                        'administrative_area' => Arr::get($address, 'province'),
                        'country_code'        => Arr::get($address, 'country_code'),
                        'country_id'          => $country->id
                    ]
                ];

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
        return $request->user()->is_root;
    }


    public function asController(ActionRequest $request): void
    {
        $this->initialisation($request);
        $this->parent = $this->customer;

        $this->handle($request);
    }
}
