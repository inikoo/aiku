<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\Shipper;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class PostmenGetTenantAddress
{
    use AsAction;
    use WithAttributes;
    public function handle(Request $request, Shipper $shipper): Shipper
    {
        $apiUrl  = "https://api.mygls.sk/ParcelService.svc?singleWsdl";
        $headers = [
            "content-type: application/json",
            "postmen-api-key: ".$shipper->data['api_key']
        ];

        $credentials_rules     = $this->get_credentials_validation($request->get('shipper'));
        $credentials_validator = validator::make($request->all(), $credentials_rules);
        if ($credentials_validator->fails()) {
            $shipper->data['errors'] = $credentials_validator->errors();
            return $shipper;
        }

        $credentials = [];
        foreach ($credentials_rules as $credential_field => $foo) {
            $credentials[$credential_field] = $request->get($credential_field);
        }
        $credentials = array_filter($credentials);

        $organisation = (new Shipper())->where('slug', $request->get('tenant'))->first();

        $params = [
            'slug'        => $request->get('shipper'),
            'description' => $request->get('label'),
            'address'     => $this->getOrganisationAddress($organisation),
            'timezone'    => 'UTC',
            'credentials' => $credentials
        ];

        $response = ProsesApiCalls::run($apiUrl.'shipper-accounts', $headers, json_encode($params));

        if ($response['status'] != 200) {
            $this->errors = [Arr::get($response, 'errors')];

            return false;
        }

        if ($response['data']['meta']['code'] != 200) {
            $this->errors = [$response['data']];
            return false;
        }

        $shipperAccount                   = new ShipperAccount();
        $shipperAccount->slug             = $request->get('shipper');
        $shipperAccount->label            = $request->get('label');
        $shipperAccount->shipper_id       = $this->shipper->id;
        $shipperAccount->organisation_id  = $organisation->id;
        $shipperAccount->data             = $response['data']['data'];
        $shipperAccount->save();

        return $shipperAccount;
    }

    private function get_credentials_validation($slug): array
    {
        return match ($slug) {
            'dpd'           => ['slid' => ['required']],
            'apc-overnight' => [
                'password'   => ['required'],
                'user_email' => ['required'],

            ],
            default => [],
        };
    }

    private function getOrganisationAddress($organisation)
    {
        $organisation_address = $organisation->data['address'];
        $organisation_country = (new Country())->where('code', $organisation_address['country_code'])->first();


        $organisation_address = [
            'country'      => $organisation_country->code_iso3,
            'street1'      => Arr::get($organisation_address, 'address_line_1'),
            'street2'      => Arr::get($organisation_address, 'address_line_2'),
            'city'         => Arr::get($organisation_address, 'locality'),
            'postal_code'  => Arr::get($organisation_address, 'postal_code'),
            'email'        => Arr::get($organisation->data, 'email'),
            'phone'        => Arr::get($organisation->data, 'phone'),
            'contact_name' => Arr::get($organisation->data, 'contact'),
            'company_name' => Arr::get($organisation->data, 'organization'),

        ];

        return array_filter($organisation_address);
    }


}
