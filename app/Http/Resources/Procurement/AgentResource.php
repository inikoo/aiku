<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 24 Feb 2023 10:14:02 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $name
 * @property string $slug
 * @property mixed $location
 * @property numeric $number_suppliers
 * @property numeric $number_supplier_products
 * @property numeric $number_purchase_orders
 */
class AgentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                     => $this->slug,
            'code'                     => $this->code,
            'name'                     => $this->name,
            'location'                 => json_decode($this->location),
            'number_suppliers'         => $this->number_suppliers,
            'number_supplier_products' => $this->number_supplier_products,
            'number_purchase_orders'   => $this->number_purchase_orders
        ];
    }
}
