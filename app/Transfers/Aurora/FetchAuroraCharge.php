<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Jul 2024 11:40:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Charge\ChargeTriggerEnum;
use App\Enums\Catalogue\Charge\ChargeTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraCharge extends FetchAurora
{
    protected function parseModel(): void
    {
        $shop = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Charge Store Key'});

        $trigger = match ($this->auroraModelData->{'Charge Trigger'}) {
            'Product'              => ChargeTriggerEnum::PRODUCT,
            'Order'                => ChargeTriggerEnum::ORDER,
            'Selected by Customer' => ChargeTriggerEnum::SELECTED_BY_CUSTOMER,
            'Payment Type'         => ChargeTriggerEnum::PAYMENT_ACCOUNT,
            default                => null
        };

        $type = match ($this->auroraModelData->{'Charge Scope'}) {
            'Hanging'   => ChargeTypeEnum::HANGING,
            'Premium'   => ChargeTypeEnum::PREMIUM,
            'Tracking'  => ChargeTypeEnum::TRACKING,
            'Pastpay'   => ChargeTypeEnum::PAYMENT,
            'Insurance' => ChargeTypeEnum::INSURANCE,
            'CoD'       => ChargeTypeEnum::COD,
            'Packing'   => ChargeTypeEnum::PACKING,
            default     => null
        };

        $settings = [
            'rules'        => $this->auroraModelData->{'Charge Terms Metadata'},
            'rule_subject' => $this->auroraModelData->{'Charge Terms Type'},
        ];

        if ($this->auroraModelData->{'Charge Scope'} != 'Pastpay') {
            $settings['amount'] = $this->auroraModelData->{'Charge Metadata'};
        }


        $description = strip_tags(html_entity_decode(htmlspecialchars_decode($this->auroraModelData->{'Charge Public Description'})));
        if ($description == '') {
            $description = strip_tags($this->auroraModelData->{'Charge Description'});
        }

        $state                      = $this->auroraModelData->{'Charge Active'} === 'Yes' ? ChargeStateEnum::ACTIVE : ChargeStateEnum::DISCONTINUED;
        $this->parsedData['shop']   = $shop;
        $this->parsedData['charge'] = [
            'code'            => $this->auroraModelData->{'Charge Scope'}.'-'.$shop->code,
            'name'            => $this->auroraModelData->{'Charge Name'},
            'description'     => $description,
            'type'            => $type,
            'trigger'         => $trigger,
            'settings'        => $settings,
            'state'           => $state,
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Charge Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now()
        ];


        $createdBy = $this->auroraModelData->{'Charge Begin Date'};

        if ($createdBy) {
            $this->parsedData['charge']['created_by'] = $createdBy;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Charge Dimension')
            ->where('Charge Key', $id)->first();
    }
}
