<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 14:09:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraCustomer extends FetchAurora
{

    protected function parseModel(): void
    {
        $status = 'approved';
        $state  = 'active';
        if ($this->auroraModelData->{'Customer Type by Activity'} == 'Rejected') {
            $status = 'rejected';
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'ToApprove') {
            $state  = 'registered';
            $status = 'pending-approval';
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Losing') {
            $state = 'losing';
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Lost') {
            $state = 'lost';
        }

       $this->parsedData['customer'] =
            [
                'name'                     => $this->auroraModelData->{'Customer Name'},
                'state'                    => $state,
                'status'                   => $status,
                'contact_name'             => $this->auroraModelData->{'Customer Main Contact Name'},
                'company_name'             => $this->auroraModelData->{'Customer Company Name'},
                'email'                    => $this->auroraModelData->{'Customer Main Plain Email'},
                'phone'                    => $this->auroraModelData->{'Customer Main Plain Mobile'},
                'identity_document_number' => Str::limit($this->auroraModelData->{'Customer Registration Number'}),
                'website'                  => $this->auroraModelData->{'Customer Website'},
                'tax_number'               => $this->auroraModelData->{'Customer Tax Number'},
                'tax_number_status'        => $this->auroraModelData->{'Customer Tax Number'} == ''
                    ? 'na'
                    : match ($this->auroraModelData->{'Customer Tax Number Valid'}) {
                        'Yes' => 'valid',
                        'No' => 'invalid',
                        default => 'unknown'
                    },
                'organisation_source_id'                => $this->auroraModelData->{'Customer Key'},
                'created_at'               => $this->auroraModelData->{'Customer First Contacted Date'}
            ]
        ;

        $this->parsedData['shop']=$this->parseShop($this->auroraModelData->{'Customer Store Key'});



        $addresses = [];

        $billingAddress  = $this->parseAddress(prefix: 'Customer Invoice', auAddressData: $this->auroraModelData);
        $deliveryAddress = $this->parseAddress(prefix: 'Customer Delivery', auAddressData: $this->auroraModelData);

        $addresses['billing'] = [
            $billingAddress
        ];
        if ($billingAddress != $deliveryAddress) {
            $addresses['delivery'] = [
                $deliveryAddress
            ];
        }
       $this->parsedData['addresses'] = $addresses;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Dimension')
            ->where('Customer Key', $id)->first();
    }

}
