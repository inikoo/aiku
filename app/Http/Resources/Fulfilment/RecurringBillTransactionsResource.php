<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 12:12:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Billables\Service;
use App\Models\Catalogue\Product;
use App\Models\Fulfilment\Pallet;
use Illuminate\Http\Resources\Json\JsonResource;

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
 */
class RecurringBillTransactionsResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($this->asset_type == 'service') {
            $unitAbbreviation = 's';
            $unitLabel        = __('service');
        } else {
            $unitAbbreviation = 'u';
            $unitLabel        = __('unit');
        }

        $item_name = '';
        $item_slug = '';
        $route = [];
        if ($this->item_type == 'Pallet') {
            $pallet = Pallet::find($this->item_id);
            if ($pallet) {
                $item_name = $pallet->customer_reference;
                $item_slug = $pallet->slug;
                $route = [
                    'name' => 'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                    'parameters' => [
                        'organisation' => $this->organisation_slug,
                        'fulfilment' => $this->fulfilment_slug,
                        'fulfilmentCustomer' => $this->fulfilment_customer_slug,
                        'pallet'    => $item_slug
                    ]
                ];
            }
        } elseif ($this->item_type == 'Service') {
            $service = Service::find($this->item_id);
            if ($service) {
                $item_name = $service->name;
                $item_slug = $service->slug;
                $route = [
                    'name' => 'grp.org.fulfilments.show.catalogue.services.show',
                    'parameters' => [
                        'organisation' => $this->organisation_slug,
                        'fulfilment' => $this->fulfilment_slug,
                        'service' => $item_slug
                    ]
                ];
            }
        } elseif ($this->item_type == 'Product') {
            $product = Product::find($this->item_id);
            if ($product) {
                $item_name = $product->name;
                $item_slug = $product->slug;
                $route = [
                    'name' => 'grp.org.fulfilments.show.catalogue.outers.show',
                    'parameters' => [
                        'organisation' => $this->organisation_slug,
                        'fulfilment' => $this->fulfilment_slug,
                        'product' => $item_slug
                    ]
                ];
            }
        }
        return [
            'id'                 => $this->id,
            'type'               => $this->item_type,
            'type_icon'          => $this->typeIcon($this->item_type),
            'asset_id'           => $this->asset_id,
            'asset_slug'         => $this->asset_slug,
            'asset_code'         => $this->asset_code,
            'asset_price'        => $this->asset_price,
            'asset_name'         => $this->asset_name,
            'asset_price'        => $this->asset_price,
            'asset_unit'         => $this->asset_unit,
            'asset_units'        => $this->asset_units,
            'currency_code'      => $this->currency_code,
            'unit_abbreviation'  => $unitAbbreviation,
            'unit_label'         => $unitLabel,
            'quantity'           => (int) $this->quantity,
            'total'              => $this->net_amount,
            'discount'           => (int) $this->discount,

            'fulfilment_customer_slug' => $this->fulfilment_customer_slug,
            'fulfilment_slug'   => $this->fulfilment_slug,
            'organisation_slug' => $this->organisation_slug,

            'item_name'          => $item_name,
            'item_slug'          => $item_slug,

            'route'              => $route
            // 'historic_assets_id'=> $this->historic_assets_id



        ];
    }

    public function typeIcon($type)
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
