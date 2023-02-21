<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 17:25:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class FetchAuroraDeletedCustomer extends FetchAurora
{

    protected function parseModel(): void
    {
        $this->parsedData['customer'] = null;
        if (!$this->auroraModelData->{'Customer Deleted Metadata'}) {
            $auroraDeletedData = new stdClass();
        } else {
            $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'Customer Deleted Metadata'}));
        }


        $status = 'approved';
        $state  = 'active';


        $taxNumber          = $auroraDeletedData->{'Customer Tax Number'} ?? null;
        $registrationNumber = $auroraDeletedData->{'Customer Registration Number'} ?? null;
        if ($registrationNumber) {
            $registrationNumber = Str::limit($registrationNumber);
        }
        $taxNumberValid = $auroraDeletedData->{'Customer Tax Number Valid'} ?? 'unknown';

        $this->parsedData['shop'] = $this->parseShop($this->auroraModelData->{'Customer Store Key'});

        $data = [
            'deleted' => ['source' => 'aurora']
        ];

        $contactName = $this->auroraModelData->{'Customer Deleted Contact Name'} ?? null;
        $name        = $this->auroraModelData->{'Customer Deleted Name'} ?? null;

        if ($contactName == $name) {
            $name = null;
        }


        $this->parsedData['customer'] =
            [

                'name'                     => $auroraDeletedData->{'Customer Name'},
                'reference'                => sprintf('%05d', $this->auroraModelData->{'Customer Key'}),
                'state'                    => $state,
                'status'                   => $status,
                'contact_name'             => $contactName,
                'company_name'             => $name,
                'email'                    => $this->auroraModelData->{'Customer Deleted Email'} ?? null,
                'phone'                    => $auroraDeletedData->{'Customer Main Plain Mobile'} ?? null,
                'identity_document_number' => $registrationNumber,
                'website'                  => $auroraDeletedData->{'Customer Website'} ?? null,
                'tax_number'               => $taxNumber,
                'tax_number_status'        => $taxNumber
                    ? 'na'
                    : match ($taxNumberValid) {
                        'Yes' => 'valid',
                        'No' => 'invalid',
                        default => 'unknown'
                    },
                'created_at'               => $auroraDeletedData->{'Customer First Contacted Date'},
                'deleted_at'               => $this->auroraModelData->{'Customer Deleted Date'},
                'source_id'                => $auroraDeletedData->{'Customer Key'},
                'data'                     => $data

            ];


        if ($auroraDeletedData->{'Customer Invoice Address Country 2 Alpha Code'} == 'XX') {
            $auroraDeletedData = $this->fixAddress(prefix: 'Customer Invoice', data: $auroraDeletedData);
        }
        if ($auroraDeletedData->{'Customer Delivery Address Country 2 Alpha Code'} == 'XX') {
            $auroraDeletedData = $this->fixAddress(prefix: 'Customer Delivery', data: $auroraDeletedData);
        }


        $billingAddress  = $this->parseAddress(prefix: 'Customer Invoice', auAddressData: $auroraDeletedData);
        $deliveryAddress = $this->parseAddress(prefix: 'Customer Delivery', auAddressData: $auroraDeletedData);

        $this->parsedData['contact_address'] = $billingAddress;


        if ($billingAddress != $deliveryAddress) {
            $this->parsedData['delivery_address'] = $deliveryAddress;
        }
    }


    protected function fixAddress($prefix, $data)
    {
        if ($data->{"$prefix Address Country 2 Alpha Code"} == 'XX' and $data->{"$prefix Address Postal Code"} == '35550') {
            $data->{"$prefix Address Country 2 Alpha Code"} = 'ES';
        } elseif ($data->{"$prefix Address Country 2 Alpha Code"} == 'XX' and
            $data->{"$prefix Address Postal Code"} == '' and
            $data->{"$prefix Address Postal Town"} == '' and
            $data->{"$prefix Address Postal Address Line 1"} == '') {
            $data->{"$prefix Address Country 2 Alpha Code"} = config('app.country');
        }


        return $data;
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Deleted Dimension')
            ->where('Customer Key', $id)->first();
    }

}
