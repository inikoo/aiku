<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 17:12:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceUpserts\Aurora\Single\UpsertCustomerClientFromSource;
use App\Actions\SourceUpserts\Aurora\Single\UpsertCustomerFromSource;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraOrder extends FetchAurora
{

    protected function parseModel(): void
    {


        if ($this->auroraModelData->{'Order Customer Client Key'} != '') {
           $parent=UpsertCustomerClientFromSource::run($this->organisationSource,$this->auroraModelData->{'Order Customer Client Key'});

        } else {

            $parent=UpsertCustomerFromSource::run($this->organisationSource,$this->auroraModelData->{'Order Customer Key'});
        }
        $this->parsedData['parent'] =$parent;

        $state = Str::snake($this->auroraModelData->{'Order State'}, '-');

        if ($state == 'approved') {
            $state = 'in-warehouse';
        }

        $this->parsedData['order'] = [
            'number'     => $this->auroraModelData->{'Order Public ID'},
            'state'      => $state,
            'organisation_source_id'  => $this->auroraModelData->{'Order Key'},
            'exchange'   => $this->auroraModelData->{'Order Currency Exchange'},
            'created_at' => $this->auroraModelData->{'Order Created Date'},

        ];

        $deliveryAddressData                 = $this->parseAddress(prefix: 'Order Delivery', auAddressData: $this->auroraModelData);
        $this->parsedData['delivery_address'] = new Address($deliveryAddressData);

        $billingAddressData                 = $this->parseAddress(prefix: 'Order Invoice', auAddressData: $this->auroraModelData);
        $this->parsedData['billing_address'] = new Address($billingAddressData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Dimension')
            ->where('Order Key', $id)->first();
    }

}
