<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class WhistlGbPrepareShipment
{
    use AsAction;
    use WithAttributes;
    public function handle($shipper, $request, $pickUp, $shipTo, $parcelsData, $cash_on_delivery): array
    {
        $shipToCountry = (new Country())->where('code', Arr::get($shipTo, 'country_code'))->first();


        $shipTo = [
            'country'      => $shipToCountry->code_iso3,
            'street1'      => Arr::get($shipTo, 'address_line_1'),
            'street2'      => Arr::get($shipTo, 'address_line_2'),
            'city'         => Arr::get($shipTo, 'locality'),
            'postal_code'  => Arr::get($shipTo, 'postal_code'),
            'email'        => Arr::get($shipTo, 'email'),
            'phone'        => Arr::get($shipTo, 'phone'),
            'contact_name' => Arr::get($shipTo, 'contact'),
            'company_name' => Arr::get($shipTo, 'organization'),

        ];

        $shipTo = array_filter($shipTo);


        $parcels    = [];
        $references = [];
        foreach ($parcelsData as $parcelData) {
            $references[] = $request->get('reference');
            $parcels[]    = [
                'box_type'  => 'custom',
                'dimension' => [
                    'width'  => $parcelData['width'],
                    'height' => $parcelData['height'],
                    'depth'  => $parcelData['depth'],
                    'unit'   => 'cm'

                ],
                'weight'    => [
                    'value' => $parcelData['weight'],
                    'unit'  => 'kg'

                ],
                'items'     => [
                    [
                        'description' => $request->get('reference').' items',
                        'quantity'    => 1,
                        'price'       => [
                            'amount'   => 0,
                            'currency' => 'GBP'
                        ],
                        'weight'      => [
                            'value' => $parcelData['weight'],
                            'unit'  => 'kg'

                        ],
                    ]
                ]
            ];
        }

        return array(
            'service_type'          => $request->get('service_type'),
            'shipper_account'       => ['id' => $shipper->id],
            'shipment'              => [
                'ship_from' => PostmenGetOrganisationAddress::run($shipper->data['organisation']),
                'ship_to'   => $shipTo,
                'parcels'   => $parcels
            ],
            'delivery_instructions' => $request->get('note'),

            'references'   => $references,
            'order_number' => $request->get('reference'),

        );
    }
}
