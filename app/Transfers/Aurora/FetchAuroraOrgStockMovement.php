<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 31 Aug 2024 11:20:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum;
use App\Models\Inventory\OrgStockMovement;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrgStockMovement extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!in_array(
            $this->auroraModelData->{'Inventory Transaction Record Type'},
            ['Movement', 'Helper']
        )) {
            return;
        }


        if (in_array($this->auroraModelData->{'Inventory Transaction Type'}, ['Move Out', 'Move In']) and
            $this->auroraModelData->{'Inventory Transaction Quantity'} == 0) {
            return;
        }

        $orgStock = $this->parseOrgStock($this->organisation->id.':'.$this->auroraModelData->{'Part SKU'});
        if (!$orgStock) {
            return;
        }


        $location = $this->parseLocation($this->organisation->id.':'.$this->auroraModelData->{'Location Key'}, $this->organisationSource);

        $date = $this->parseDatetime($this->auroraModelData->{'Date'});

        $type        = null;
        $isDelivered = false;

        if ($this->auroraModelData->{'Inventory Transaction Type'} == 'Sale') {
            $type        = OrgStockMovementTypeEnum::PICKED;
            $isDelivered = true;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Adjust') {
            $type = OrgStockMovementTypeEnum::ADJUSTMENT;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'In') {
            $type = OrgStockMovementTypeEnum::PURCHASE;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Found') {
            $type = OrgStockMovementTypeEnum::FOUND;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Restock') {
            $type = OrgStockMovementTypeEnum::RETURN_PICKED;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Other Out') {
            if ($this->auroraModelData->{'Inventory Transaction Section'} == 'Lost') {
                $type = OrgStockMovementTypeEnum::WRITE_OFF;
            }
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'FailSale') {
            if ($this->auroraModelData->{'Inventory Transaction Record Type'} == 'Movement') {
                $type = OrgStockMovementTypeEnum::PICKED;
            } else {
                return;
            }
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Production') {
            if ($this->auroraModelData->{'Inventory Transaction Section'} == 'In') {
                $type = OrgStockMovementTypeEnum::RETURN_CONSUMPTION;
            } else {
                $type = OrgStockMovementTypeEnum::CONSUMPTION;
            }
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Move In') {
            $type = OrgStockMovementTypeEnum::LOCATION_TRANSFER;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Move Out') {
            $type = OrgStockMovementTypeEnum::LOCATION_TRANSFER;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Lost') {
            $type = OrgStockMovementTypeEnum::WRITE_OFF;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Broken') {
            $type = OrgStockMovementTypeEnum::WRITE_OFF;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Associate') {
            $type = OrgStockMovementTypeEnum::ASSOCIATE;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Disassociate') {
            $type = OrgStockMovementTypeEnum::DISASSOCIATE;
        } elseif ($this->auroraModelData->{'Inventory Transaction Type'} == 'Audit') {
            $type = OrgStockMovementTypeEnum::DISASSOCIATE;
        }
        if (!$type) {
            dd($this->auroraModelData);
        }


        $this->parsedData['orgStock'] = $orgStock;
        $this->parsedData['location'] = $location;


        $this->parsedData['orgStockMovement'] = [
            'is_delivered'    => $isDelivered,
            'type'            => $type,
            'quantity'        => $this->auroraModelData->{'Inventory Transaction Quantity'},
            'amount'          => $this->auroraModelData->{'Inventory Transaction Amount'},
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Inventory Transaction Key'},
            'date'            => $date,
            'fetched_at'      => now(),
            'last_fetched_at' => now()
        ];

        //dd($this->parsedData['orgStockMovement']);
        // print_r($this->auroraModelData);
        // exit;
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Inventory Transaction Fact')
            ->where('Inventory Transaction Key', $id)->first();
    }
}
