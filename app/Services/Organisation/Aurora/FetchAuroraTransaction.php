<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:52:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\OMS\Transaction\TransactionStateEnum;
use App\Enums\OMS\Transaction\TransactionStatusEnum;
use App\Enums\OMS\Transaction\TransactionTypeEnum;
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
            if (class_basename($historicItem) == 'HistoricOuterable') {
                $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
                    'In Process'            => TransactionStateEnum::CREATING,
                    'Submitted by Customer' => TransactionStateEnum::SUBMITTED,
                    'Ready to Pick', 'Picking', 'Ready to Pack', 'Packing', 'Packed', 'Packed Done' => TransactionStateEnum::HANDLING,
                    'Ready to Ship' => TransactionStateEnum::FINALISED,
                    'Dispatched', 'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other', 'Cancelled', 'Suspended', 'Cancelled by Customer' => TransactionStateEnum::SETTLED,
                    'Unknown' => null
                };
            } elseif (class_basename($historicItem) == 'HistoricService') {
                $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
                    'In Process' => TransactionStateEnum::CREATING,
                    'Dispatched', 'Cancelled', 'Suspended', 'Cancelled by Customer' => TransactionStateEnum::SETTLED,
                    'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other', 'Unknown' => null,
                    default => TransactionStateEnum::SUBMITTED,
                };
            }


            $status = match ($this->auroraModelData->{'Current Dispatching State'}) {
                'Dispatched' => TransactionStatusEnum::DISPATCHED,
                'Cancelled', 'Suspended', 'Cancelled by Customer' => TransactionStatusEnum::CANCELLED,
                'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other', 'Unknown' => TransactionStatusEnum::FAIL,
                default => TransactionStatusEnum::PROCESSING,
            };

            if ($status == TransactionStatusEnum::DISPATCHED and $this->auroraModelData->{'No Shipped Due Out of Stock'} > 0) {
                $status = TransactionStatusEnum::DISPATCHED_WITH_MISSING;
            }


            $this->parsedData['transaction'] = [
                'type'                => TransactionTypeEnum::ORDER,
                'item_type'           => class_basename($historicItem),
                'item_id'             => $historicItem->id,
                'tax_band_id'         => $taxBand->id ?? null,
                'state'               => $state,
                'status'              => $status,
                'quantity_ordered'    => $this->auroraModelData->{'Order Quantity'},
                'quantity_bonus'      => $this->auroraModelData->{'Order Bonus Quantity'},
                'quantity_dispatched' => $this->auroraModelData->{'Delivery Note Quantity'},
                'quantity_fail'       => $this->auroraModelData->{'No Shipped Due Out of Stock'},


                'discounts'                => $this->auroraModelData->{'Order Transaction Total Discount Amount'},
                'net'                      => $this->auroraModelData->{'Order Transaction Amount'},
                'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'},

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
