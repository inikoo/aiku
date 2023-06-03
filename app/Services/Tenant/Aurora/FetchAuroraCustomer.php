<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 14:09:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Tenant\Aurora;

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraCustomer extends FetchAurora
{
    protected function parseModel(): void
    {
        $status = CustomerStatusEnum::APPROVED;
        $state  = CustomerStateEnum::ACTIVE;
        if ($this->auroraModelData->{'Customer Type by Activity'} == 'Rejected') {
            $status = CustomerStatusEnum::REJECTED;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'ToApprove') {
            $state  = CustomerStateEnum::REGISTERED;
            $status = CustomerStatusEnum::PENDING_APPROVAL;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Losing') {
            $state = CustomerStateEnum::LOSING;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Lost') {
            $state = CustomerStateEnum::LOST;
        }


        $this->parsedData['customer'] =
            [
                'reference'                => sprintf('%05d', $this->auroraModelData->{'Customer Key'}),
                'state'                    => $state,
                'status'                   => $status,
                'contact_name'             => $this->auroraModelData->{'Customer Main Contact Name'},
                'company_name'             => $this->auroraModelData->{'Customer Company Name'},
                'email'                    => $this->auroraModelData->{'Customer Main Plain Email'},
                'phone'                    => $this->auroraModelData->{'Customer Main Plain Mobile'},
                'identity_document_number' => Str::limit($this->auroraModelData->{'Customer Registration Number'}),
                'contact_website'          => $this->auroraModelData->{'Customer Website'},
                'source_id'                => $this->auroraModelData->{'Customer Key'},
                'created_at'               => $this->auroraModelData->{'Customer First Contacted Date'}
            ];

        $this->parsedData['shop'] = $this->parseShop($this->auroraModelData->{'Customer Store Key'});


        $billingAddress  = $this->parseAddress(prefix: 'Customer Invoice', auAddressData: $this->auroraModelData);
        $deliveryAddress = $this->parseAddress(prefix: 'Customer Delivery', auAddressData: $this->auroraModelData);

        $this->parsedData['contact_address'] = $billingAddress;


        $this->parsedData['tax_number'] = $this->parseTaxNumber(
            number: $this->auroraModelData->{'Customer Tax Number'},
            countryID: $billingAddress['country_id'],
            rawData: (array)$this->auroraModelData
        );


        if ($billingAddress != $deliveryAddress) {
            $this->parsedData['delivery_address'] = $deliveryAddress;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Dimension')
            ->where('Customer Key', $id)->first();
    }
}
