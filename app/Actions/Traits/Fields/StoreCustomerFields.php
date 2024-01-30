<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 Jan 2024 20:06:16 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Fields;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;

trait StoreCustomerFields
{
    private function getBlueprint(Shop $shop): array
    {
        return [
            [
                'title'  => __('contact'),
                'fields' => [
                    'company_name' => [
                        'type'  => 'input',
                        'label' => __('company')
                    ],
                    'contact_name' => [
                        'type'  => 'input',
                        'label' => __('contact name')
                    ],
                    'address'      => [
                        'type'    => 'address',
                        'label'   => __('Address'),
                        'value'   => AddressFormFieldsResource::make(
                            new Address(
                                [
                                    'country_id' => $shop->country_id,

                                ]
                            )
                        )->getArray(),
                        'options' => [
                            'countriesAddressData' => GetAddressData::run()

                        ]
                    ]
                ]
            ]
        ];
    }

}
