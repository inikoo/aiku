<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipment\ApiCalls;

use App\Models\Assets\Country;
use App\Models\Dispatch\Shipper;
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

        $tenant = (new Shipper())->where('slug', $request->get('tenant'))->first();

        $params = [
            'slug'        => $request->get('shipper'),
            'description' => $request->get('label'),
            'address'     => $this->get_tenant_address($tenant),
            'timezone'    => 'UTC',
            'credentials' => $credentials
        ];

        $response = $this->callApi($this->api_url.'shipper-accounts', $this->headers, json_encode($params));

        if ($response['status'] != 200) {
            $this->errors = [Arr::get($response, 'errors')];

            return false;
        }

        if ($response['data']['meta']['code'] != 200) {
            $this->errors = [$response['data']];
            return false;
        }

        $shipperAccount             = new ShipperAccount;
        $shipperAccount->slug       = $request->get('shipper');
        $shipperAccount->label      = $request->get('label');
        $shipperAccount->shipper_id = $this->shipper->id;
        $shipperAccount->tenant_id  = $tenant->id;
        $shipperAccount->data       = $response['data']['data'];
        $shipperAccount->save();

        return $shipperAccount;
    }

    private function get_credentials_validation($slug): array
    {
        return match ($slug) {
            'dpd' => ['slid' => ['required']],
            'apc-overnight' => [
                'password' => ['required'],
                'user_email' => ['required'],

            ],
            default => [],
        };
    }

    private function get_tenant_address($tenant)
    {
        $tenant_address = $tenant->data['address'];
        $tenant_country = (new Country)->where('code', $tenant_address['country_code'])->first();


        $tenant_address = [
            'country'      => $tenant_country->code_iso3,
            'street1'      => Arr::get($tenant_address, 'address_line_1'),
            'street2'      => Arr::get($tenant_address, 'address_line_2'),
            'city'         => Arr::get($tenant_address, 'locality'),
            'postal_code'  => Arr::get($tenant_address, 'postal_code'),
            'email'        => Arr::get($tenant->data, 'email'),
            'phone'        => Arr::get($tenant->data, 'phone'),
            'contact_name' => Arr::get($tenant->data, 'contact'),
            'company_name' => Arr::get($tenant->data, 'organization'),

        ];

        return array_filter($tenant_address);
    }


}
