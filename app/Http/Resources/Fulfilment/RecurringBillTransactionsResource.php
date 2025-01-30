<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 12:12:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Actions\Utils\Abbreviate;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
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
        $desc_a = '';
        $desc_b = '';
        $desc_c = '';
        if ($this->item_type == 'Pallet') {
            $pallet = Pallet::find($this->item_id);
            $desc_b = $pallet;

            $desc_a = __('Storage');

        } else {
            $palletDelivery = PalletDelivery::where('recurring_bill_id', $this->recurring_bill_id)->first();
            $palletReturn = PalletReturn::where('recurring_bill_id', $this->recurring_bill_id)->first();

            if ($palletDelivery) {
                $desc_b = $palletDelivery;
                $desc_a = __('Pallet Delivery');
            } elseif ($palletReturn) {
                $desc_b = $palletReturn;
                $desc_a = __('Pallet Return');
            }
        }

        if ($this->start_date) {
            $desc_c .= Carbon::parse($this->start_date)->format('d M Y') . '-';
        }
        if ($this->end_date) {
            $desc_c .= Carbon::parse($this->end_date)->format('d M Y') . ')';
        } else {
            $desc_c .= __('ongoing');
        }

        $unitAbbreviation = Abbreviate::run($this->asset_unit);


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
            'quantity'           =>   (int) $this->quantity * $this->temporal_quantity,
            'total'              => $this->net_amount,
            'discount'           => (int) $this->discount,
            // 'description'        => $description,
            'description'         => [
                'a' => $desc_a,
                'b' => $desc_b,
                'c' => $desc_c,
            ]

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
