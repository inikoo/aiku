<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 14:09:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
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

        $billingAddress  = $this->parseAddress(prefix: 'Customer Invoice', auAddressData: $this->auroraModelData);
        $deliveryAddress = $this->parseAddress(prefix: 'Customer Delivery', auAddressData: $this->auroraModelData);

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


        $this->parsedData['shop'] = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Customer Store Key'});
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Dimension')
            ->where('Customer Key', $id)->first();
    }
}
