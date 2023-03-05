<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:52:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraTransaction extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Key'}) {
            //enum('In Process by Customer','Submitted by Customer','In Process','Ready to Pick','Picking','Ready to Pack','Ready to Ship','Dispatched','Unknown','Packing','Packed','Packed Done','Cancelled','No Picked Due Out of Stock','No Picked Due No Authorised','No Picked Due Not Found','No Picked Due Other','Suspended','Cancelled by Customer','Out of Stock in Basket')
            if (in_array($this->auroraModelData->{'Current Dispatching State'}, ['Out of Stock in Basket', 'In Process by Customer'])) {
                return;
            }


            $historicItem = $this->parseHistoricItem($this->auroraModelData->{'Product Key'});

            $state = null;
            if (class_basename($historicItem) == 'HistoricProduct') {
                $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
                    'Submitted by Customer', 'In Process' => 'submitted',
                    'Ready to Pick', 'Picking', 'Ready to Pack', 'Packing', 'Packed', 'Packed Done' => 'in-warehouse',
                    'Ready to Ship' => 'finalised',
                    'Dispatched'    => 'dispatched',
                    'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other' => 'no-dispatched',
                    'Cancelled', 'Suspended', 'Cancelled by Customer' => 'cancelled',
                    'Unknown' => null
                };
            } elseif (class_basename($historicItem) == 'HistoricService') {
                $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
                    'Dispatched' => 'dispatched',
                    'Cancelled', 'Suspended', 'Cancelled by Customer' => 'cancelled',
                    'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other', 'Unknown' => null,
                    default => 'submitted'
                };
            }


            $this->parsedData['transaction'] = [
                'item_type'   => class_basename($historicItem),
                'item_id'     => $historicItem->id,
                'tax_band_id' => $taxBand->id ?? null,
                'state'       => $state,
                'quantity'    => $this->auroraModelData->{'Order Quantity'},
                'discounts'   => $this->auroraModelData->{'Order Transaction Total Discount Amount'},
                'net'         => $this->auroraModelData->{'Order Transaction Amount'},
                'source_id'   => $this->auroraModelData->{'Order Transaction Fact Key'},

            ];
        } else {
            print "Warning Product Key missing in transaction >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }
}
