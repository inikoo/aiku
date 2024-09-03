<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 23:18:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Ordering\Transaction\TransactionFailStatusEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FetchAuroraNoProductTransaction extends FetchAurora
{
    protected function parseModel(): void
    {

        dd($this->auroraModelData);


        $historicAsset = $this->parseHistoricAsset(
            $this->organisation,
            $this->auroraModelData->{'Product Key'}
        );

        //enum('In Process by Customer','Submitted by Customer','In Process','Ready to Pick','Picking','Ready to Pack','Ready to Ship','Dispatched','Unknown','Packing','Packed','Packed Done','Cancelled','No Picked Due Out of Stock','No Picked Due No Authorised','No Picked Due Not Found','No Picked Due Other','Suspended','Cancelled by Customer','Out of Stock in Basket')

        $state = match ($this->auroraModelData->{'Current Dispatching State'}) {
            'In Process by Customer' => TransactionStateEnum::CREATING,
            'In Process', 'Submitted by Customer' => TransactionStateEnum::SUBMITTED,
            'Ready to Pick', 'Picking', 'Ready to Pack', 'Packing', 'Packed', 'Packed Done' => TransactionStateEnum::HANDLING,
            'Ready to Ship' => TransactionStateEnum::FINALISED,
            'Dispatched'    => TransactionStateEnum::DISPATCHED,
            'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other', 'Cancelled', 'Suspended', 'Cancelled by Customer' => TransactionStateEnum::CANCELLED,
            'Unknown' => null
        };

        $status = match ($this->auroraModelData->{'Current Dispatching State'}) {
            'In Process by Customer', 'Out of Stock in Basket' => TransactionStatusEnum::CREATING,
            'Picking','Ready to Pack', 'No Picked Due Out of Stock', 'No Picked Due No Authorised', 'No Picked Due Not Found', 'No Picked Due Other' => TransactionStatusEnum::PROCESSING,
            'Suspended', 'Dispatched', 'Unknown', 'Cancelled' => TransactionStatusEnum::SETTLED,
        };


        $failStatus = match ($this->auroraModelData->{'Current Dispatching State'}) {
            'No Picked Due Out of Stock'  => TransactionFailStatusEnum::OUT_OF_STOCK,
            'No Picked Due No Authorised' => TransactionFailStatusEnum::NO_AUTHORISED,
            'No Picked Due Not Found'     => TransactionFailStatusEnum::NOT_FOUND,
            'No Picked Due Other'         => TransactionFailStatusEnum::OTHER,
            default                       => null,
        };





        $date = $this->parseDate($this->auroraModelData->{'Order Date'});
        $date = new Carbon($date);

        $this->parsedData['historic_asset'] = $historicAsset;

        $quantityFail = round($this->auroraModelData->{'No Shipped Due Out of Stock'}, 4);
        if ($quantityFail < 0.001) {
            $quantityFail = 0;
        }

        $quantityBonus = round($this->auroraModelData->{'Order Bonus Quantity'}, 4);
        if ($quantityBonus < 0.001) {
            $quantityBonus = 0;
        }

        $taxCategory = $this->parseTaxCategory($this->auroraModelData->{'Order Transaction Tax Category Key'});

        $this->parsedData['transaction'] = [
            'date'                => $date,
            'created_at'          => $date,
            'tax_category_id'     => $taxCategory->id,
            'state'               => $state,
            'status'              => $status,
            'quantity_ordered'    => $this->auroraModelData->{'Order Quantity'},
            'quantity_bonus'      => $quantityBonus,
            'quantity_dispatched' => $this->auroraModelData->{'Delivery Note Quantity'},
            'quantity_fail'       => $quantityFail,
            'gross_amount'        => $this->auroraModelData->{'Order Transaction Gross Amount'},
            'net_amount'          => $this->auroraModelData->{'Order Transaction Amount'},
            'source_id'           => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'},
            'fail_status'         => $failStatus,
            'fetched_at'          => now(),
            'last_fetched_at'     => now(),

        ];

    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order No Product Transaction Fact')
            ->where('Order No Product Transaction Fact Key', $id)->first();
    }
}