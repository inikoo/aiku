<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 23:18:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\Ordering\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FetchAuroraNoProductTransaction extends FetchAurora
{
    protected function parseNoProductTransaction(Order $order): void
    {
        $shippingZone = null;
        $charge       = null;

        if (in_array($this->auroraModelData->{'Transaction Type'}, ['Adjust', 'Credit'])) {
            $adjust                         = $this->parseAdjustment($this->organisation->id.':'.$this->auroraModelData->{'Order No Product Transaction Fact Key'});
            $this->parsedData['adjustment'] = $adjust;

            $net                      = $this->auroraModelData->{'Transaction Invoice Net Amount'};
            $gross                    = $net;
            $this->parsedData['type'] = 'Adjustment';
        } elseif ($this->auroraModelData->{'Transaction Type'} == 'Shipping') {
            if ($this->auroraModelData->{'Transaction Type Key'}) {
                $shippingZone = $this->parseShippingZone($this->organisation->id.':'.$this->auroraModelData->{'Transaction Type Key'});
            }


            $gross                    = $this->auroraModelData->{'Transaction Gross Amount'};
            $net                      = $this->auroraModelData->{'Transaction Net Amount'};
            $this->parsedData['type'] = $this->auroraModelData->{'Transaction Type'};
        } elseif ($this->auroraModelData->{'Transaction Type'} == 'Charges') {
            if ($this->auroraModelData->{'Transaction Type Key'}) {
                $charge = $this->parseCharge($this->organisation->id.':'.$this->auroraModelData->{'Transaction Type Key'});
            }

            $gross                    = $this->auroraModelData->{'Transaction Gross Amount'};
            $net                      = $this->auroraModelData->{'Transaction Net Amount'};
            $this->parsedData['type'] = $this->auroraModelData->{'Transaction Type'};
        } elseif ($this->auroraModelData->{'Transaction Type'} == 'Refund') {
            return;
        } else {
            dd($this->auroraModelData);
        }


        $date = $this->parseDate($this->auroraModelData->{'Order Date'});
        $date = new Carbon($date);


        $taxCategoryID = $this->auroraModelData->{'Order No Product Transaction Tax Category Key'};

        if (!$taxCategoryID) {
            $taxCategoryID = $order->tax_category_id;
        } else {
            $taxCategory   = $this->parseTaxCategory($taxCategoryID);
            $taxCategoryID = $taxCategory->id;
        }


        $this->parsedData['transaction'] = [
            'date'            => $date,
            'created_at'      => $date,
            'tax_category_id' => $taxCategoryID,
            'gross_amount'    => $gross,
            'net_amount'      => $net,
            'alt_source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Order No Product Transaction Fact Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
        ];

        if ($shippingZone and $shippingZone->shop_id==$order->shop_id) {

            $this->parsedData['transaction']['shipping_zone_id'] = $shippingZone->id;
        }

        if ($charge) {
            $this->parsedData['transaction']['charge_id'] = $charge->id;
        }
    }

    public function fetchNoProductTransaction(int $id, Order $order): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseNoProductTransaction($order);
        }

        return $this->parsedData;
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order No Product Transaction Fact')
            ->where('Order No Product Transaction Fact Key', $id)->first();
    }
}
