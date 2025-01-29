<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 12:12:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Actions\Utils\Abbreviate;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Fulfilment\Pallet;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $asset_id
 * @property mixed $asset_type*@property mixed $id
 * @property mixed $asset_slug
 * @property mixed $asset_code
 * @property mixed $asset_name
 * @property mixed $asset_price
 * @property mixed $asset_unit
 * @property mixed $asset_units
 * @property mixed $currency_code
 * @property mixed $quantity
 * @property mixed $id
 * @property mixed $is_auto_assign
 * @property mixed $historic_assets_id
 * @property mixed $temporal_quantity
 * @property mixed $item_type
 * @property mixed $net_amount
 * @property mixed $discount
 * @property mixed $item_id
 * @property mixed $start_date
 * @property mixed $end_date
 */
class RecurringBillTransactionsResource extends JsonResource
{
    public function toArray($request): array
    {
        $description='';

        if($this->item_type=='Pallet'){
            $pallet=Pallet::find($this->item_id);

            $description=__('Storage').': '.$pallet->reference;
            if($this->start_date){
                $description .= ' (' . Carbon::parse($this->start_date)->format('d M Y') . '-';
            } if($this->end_date){
                $description .= Carbon::parse($this->end_date)->format('d M Y') . ')';
            }else{
                $description .= __('ongoing').')';
            }


        }

        $unitAbbreviation=Abbreviate::run($this->asset_unit);


        return [
            'id'                 => $this->id,
            'type'               => $this->item_type,
            'type_icon'          => $this->typeIcon($this->item_type),
            'asset_id'           => $this->asset_id,
            'asset_slug'         => $this->asset_slug,
            'asset_code'         => $this->asset_code,
            'asset_price'        => $this->asset_price,
            'asset_name'         => $this->asset_name,
            'asset_unit'         => $this->asset_unit,
            'asset_units'        => $this->asset_units,
            'currency_code'      => $this->currency_code,
            'unit_abbreviation'  => $unitAbbreviation,
            'unit_label'         => $this->asset_unit,
            'quantity'           =>   (int) $this->quantity*$this->temporal_quantity,
            'total'              => $this->net_amount,
            'discount'           => (int) $this->discount,
            'description'=>$description

        ];
    }

    public function typeIcon($type): ?array
    {
        if ($type == 'Pallet') {
            return [
                'tooltip' => __('Pallet'),
                'icon'    => 'fal fa-pallet',
                // 'app'     => [
                //     'name' => 'bell',
                //     'type' => 'font-awesome-5'
                // ]
            ];
        } else {
            return null;
        }
    }
}
