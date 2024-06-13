<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Enums\Ordering\Transaction\TransactionTypeEnum;
use Illuminate\Support\Carbon;
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


            $historicItem = $this->parseTransactionItem(
                $this->organisation,
                $this->auroraModelData->{'Product Key'}
            );

            $state = null;
            if (class_basename($historicItem) == 'HistoricAsset') {
                $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
                    'In Process'            => TransactionStateEnum::CREATING,
                    'Submitted by Customer' => TransactionStateEnum::SUBMITTED,
                    'Ready to Pick', 'Picking', 'Ready to Pack', 'Packing', 'Packed', 'Packed Done' => TransactionStateEnum::HANDLING,
                    'Ready to Ship' => TransactionStateEnum::FINALISED,
                    'Dispatched'    => TransactionStateEnum::DISPATCHED,
                    'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other', 'Cancelled', 'Suspended', 'Cancelled by Customer' => TransactionStateEnum::CANCELLED,
                    'Unknown' => null
                };
            } elseif (class_basename($historicItem) == 'HistoricService') {
                $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
                    'In Process' => TransactionStateEnum::CREATING,
                    'Dispatched' => TransactionStateEnum::DISPATCHED,
                    'Cancelled', 'Suspended', 'Cancelled by Customer' => TransactionStateEnum::CANCELLED,
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

            $date = $this->parseDate($this->auroraModelData->{'Order Date'});
            $date = new Carbon($date);

            $this->parsedData['item'] = $historicItem;

            $quantityFail = round($this->auroraModelData->{'No Shipped Due Out of Stock'}, 4);
            if ($quantityFail < 0.001) {
                $quantityFail = 0;
            }

            $quantityBonus = round($this->auroraModelData->{'Order Bonus Quantity'}, 4);
            if ($quantityBonus < 0.001) {
                $quantityBonus = 0;
            }


            $this->parsedData['transaction'] = [
                'tax_rate'            => $this->auroraModelData->{'Transaction Tax Rate'},
                'date'                => $date,
                'created_at'          => $date,
                'type'                => TransactionTypeEnum::ORDER,
                'tax_band_id'         => $taxBand->id ?? null,
                'state'               => $state,
                'status'              => $status,
                'quantity_ordered'    => $this->auroraModelData->{'Order Quantity'},
                'quantity_bonus'      => $quantityBonus,
                'quantity_dispatched' => $this->auroraModelData->{'Delivery Note Quantity'},
                'quantity_fail'       => $quantityFail,
                'discounts'           => $this->auroraModelData->{'Order Transaction Total Discount Amount'},
                'net'                 => $this->auroraModelData->{'Order Transaction Amount'},
                'source_id'           => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'},

            ];
        } else {
            print "Warning Asset Key missing in transaction >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }
}