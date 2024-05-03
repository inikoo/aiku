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
 * @property string $agent_name
 * @property string $slug
 * @property string $org_slug
 * @property string $supplier_locations
 * @property string $number_supplier_products
 * @property string $number_purchase_orders
 * @property string $created_at
 * @property string $updated_at
 * @property string $agent_slug
 */
class SupplierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'org_slug'                 => $this->org_slug,
            'agent_slug'               => $this->agent_slug,
            'code'                     => $this->code,
            'name'                     => $this->name,
            'agent_name'               => $this->agent_name,
            'number_supplier_products' => $this->number_supplier_products,
            'number_purchase_orders'   => $this->number_purchase_orders,
            'slug'                     => $this->slug,
            'supplier_locations'       => json_decode($this->supplier_locations),
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
        ];
    }
}
