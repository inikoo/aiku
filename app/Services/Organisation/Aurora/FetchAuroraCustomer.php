<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 14:09:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraCustomer extends FetchAurora
{
    protected function parseModel(): void
    {

        $this->parsedData['shop'] = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Customer Store Key'});

        $status = CustomerStatusEnum::APPROVED->value;
        $state  = CustomerStateEnum::ACTIVE->value;
        if ($this->auroraModelData->{'Customer Type by Activity'} == 'Rejected') {
            $status = CustomerStatusEnum::REJECTED->value;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'ToApprove') {
            $state  = CustomerStateEnum::REGISTERED->value;
            $status = CustomerStatusEnum::PENDING_APPROVAL->value;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Losing') {
            $state = CustomerStateEnum::LOSING->value;
        } elseif ($this->auroraModelData->{'Customer Type by Activity'} == 'Lost') {
            $state = CustomerStateEnum::LOST->value;
        }

        $billingAddress  = $this->parseAddress(prefix: 'Customer Invoice', auAddressData: $this->auroraModelData);
        $deliveryAddress = $this->parseAddress(prefix: 'Customer Delivery', auAddressData: $this->auroraModelData);

        if(Arr::get($billingAddress, 'country_id') == null) {
            $billingAddress['country_id'] = $this->parsedData['shop']->country_id;
        }
        if(Arr::get($deliveryAddress, 'country_id') == null) {
            $deliveryAddress['country_id'] = $this->parsedData['shop']->country_id;
        }

        $taxNumber = $this->parseTaxNumber(
            number: $this->auroraModelData->{'Customer Tax Number'},
            countryID: $billingAddress['country_id'],
            rawData: (array)$this->auroraModelData
        );

        $contactName = $this->auroraModelData->{'Customer Main Contact Name'};
        $company     = $this->auroraModelData->{'Customer Company Name'};

        if (!$company and !$contactName) {
            $contactName = $this->auroraModelData->{'Customer Name'};
            if (!$contactName) {
                $contactName = $this->auroraModelData->{'Customer Main Plain Email'};
            }
            if (!$contactName) {
                $contactName = 'Unknown';
            }
        }

        $this->parsedData['customer'] =
            [
                'reference'                => sprintf('%05d', $this->auroraModelData->{'Customer Key'}),
                'state'                    => $state,
                'status'                   => $status,
                'contact_name'             => $contactName,
                'company_name'             => $company,
                'email'                    => $this->auroraModelData->{'Customer Main Plain Email'},
                'phone'                    => $this->auroraModelData->{'Customer Main Plain Mobile'},
                'identity_document_number' => Str::limit($this->auroraModelData->{'Customer Registration Number'}),
                'contact_website'          => $this->auroraModelData->{'Customer Website'},
                'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Customer Key'},
                'created_at'               => $this->auroraModelData->{'Customer First Contacted Date'},
                'contact_address'          => $billingAddress,
                'tax_number'               => $taxNumber
            ];

        if ($billingAddress != $deliveryAddress) {
            $this->parsedData['customer']['delivery_address'] = $deliveryAddress;
        }


    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Dimension')
            ->where('Customer Key', $id)->first();
    }
}
