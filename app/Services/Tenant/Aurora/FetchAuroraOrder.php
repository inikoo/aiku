<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 17:12:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchCustomers;
use App\Actions\SourceFetch\Aurora\FetchCustomerClients;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraOrder extends FetchAurora
{

    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Order Customer Client Key'} != '') {
            $parent = FetchCustomerClients::run($this->tenantSource, $this->auroraModelData->{'Order Customer Client Key'});
        } else {
            $parent = FetchCustomers::run($this->tenantSource, $this->auroraModelData->{'Order Customer Key'});
        }
        $this->parsedData['parent'] = $parent;

        $state = Str::snake($this->auroraModelData->{'Order State'}, '-');

        if ($state == 'approved') {
            $state = 'in-warehouse';
        }

        $this->parsedData['order'] = [
            'number'     => $this->auroraModelData->{'Order Public ID'},
            'state'      => $state,
            'source_id'  => $this->auroraModelData->{'Order Key'},
            'exchange'   => $this->auroraModelData->{'Order Currency Exchange'},
            'created_at' => $this->auroraModelData->{'Order Created Date'},

        ];

        $deliveryAddressData                  = $this->parseAddress(prefix: 'Order Delivery', auAddressData: $this->auroraModelData);
        $this->parsedData['delivery_address'] = new Address($deliveryAddressData);

        $billingAddressData                  = $this->parseAddress(prefix: 'Order Invoice', auAddressData: $this->auroraModelData);
        $this->parsedData['billing_address'] = new Address($billingAddressData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Dimension')
            ->where('Order Key', $id)->first();
    }

}
