<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Jul 2024 11:40:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Charge\ChargeTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraCharge extends FetchAurora
{
    protected function parseModel(): void
    {
        $shop = $this->parseShop($this->auroraModelData->{'Charge Store Key'});


        $type = match ($this->auroraModelData->{'Charge Scope'}) {
            'Hanging'  => ChargeTypeEnum::HANGING,
            'Premium'  => ChargeTypeEnum::PREMIUM,
            'Tracking' => ChargeTypeEnum::TRACKING,
            'Pastpay'  => ChargeTypeEnum::PASTPAY,
            'CoD'      => ChargeTypeEnum::COD,
            default    => null
        };

        $this->parsedData['model'] = $this->auroraModelData->{'Charge Scope'} == 'Insurance' ? 'Insurance' : 'Charge';

        $settings = [
            'rules'        => $this->auroraModelData->{'Charge Terms Metadata'},
            'rule_subject' => $this->auroraModelData->{'Charge Terms Type'},
        ];

        $state = $this->auroraModelData->{'Charge Active'} === 'Yes' ? ChargeStateEnum::ACTIVE : ChargeStateEnum::DISCONTINUED;

        $this->parsedData['modelData'] = [
            'code'        => $this->auroraModelData->{'Charge Scope'}.'-'.$shop->code,
            'name'        => $this->auroraModelData->{'Charge Name'},
            'description' => $this->auroraModelData->{'Charge Public Description'},
            'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Charge Key'},
            'type'        => $type,
            'created_at'  => $this->parseDatetime($this->auroraModelData->{'Charge Begin Date'}),
            'settings'    => $settings,
            'state'       => $state
        ];

        if ($type != ChargeTypeEnum::PASTPAY) {
            $this->parsedData['modelData']['price'] = $this->auroraModelData->{'Charge Metadata'};
        }


        $createdBy = $this->auroraModelData->{'Clocking Machine Creation Date'};

        if ($createdBy) {
            $this->parsedData['clocking-machine']['created_by'] = $createdBy;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Clocking Machine Dimension')
            ->where('Clocking Machine Key', $id)->first();
    }
}
