<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Transfers\Aurora\FetchAurora;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedInvoice extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!$this->auroraModelData->{'Invoice Deleted Order Key'}) {
            print "Deleted invoice dont have order key\n";

            return;
        }

        if ($order = $this->parseOrder($this->organisation->id.':'.$this->auroraModelData->{'Invoice Deleted Order Key'})) {
            $this->parsedData['order'] = $order;
        } else {
            print "Deleted invoice order not found\n";

            return;
        }


        $auroraDeletedData = json_decode($this->auroraModelData->{'Invoice Deleted Metadata'});

        $deleted_at = $this->auroraModelData->{'Invoice Deleted Date'};
        if ($deleted_at == '0000-00-00 00:00:00') {
            $deleted_at = $auroraDeletedData->{'Invoice Date'};
        }

        if (!$deleted_at) {
            print "Deleted stock no date\n";

            return;
        }

        $data = [
            'deleted' => [
                'legacy' => [
                    'items' => $auroraDeletedData->items,
                ],
                'note'   => $this->auroraModelData->{'Invoice Deleted Note'}
            ]
        ];


        $this->parsedData['note'] = $this->auroraModelData->{'Invoice Deleted Note'};

        $this->parsedData['invoice'] =
            [
                'number'     => $this->auroraModelData->{'Invoice Deleted Public ID'},
                'type'       => strtolower($this->auroraModelData->{'Invoice Deleted Type'}),
                'exchange'   => $auroraDeletedData->{'Invoice Currency Exchange'},
                'created_at' => $auroraDeletedData->{'Invoice Date'},
                'deleted_at' => $deleted_at,
                'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Invoice Deleted Key'},
                'net'        => $auroraDeletedData->{'Invoice Total Net Amount'},
                'total'      => $this->auroraModelData->{'Invoice Deleted Total Amount'},
                'data'       => $data
            ];


        $billingAddressData                  = $this->parseAddress(prefix: 'Invoice', auAddressData: $auroraDeletedData);
        $this->parsedData['billing_address'] = new Address($billingAddressData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Invoice Deleted Dimension')
            ->where('Invoice Deleted Key', $id)->first();
    }
}
