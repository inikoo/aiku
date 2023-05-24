<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:30:19 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $number
 * @property string $created_at
 * @property string $updated_at
 * @property string $slug
 * @property string $date
 */
class PurchaseOrderResource extends JsonResource
{
    public function toArray($request): array
    {


        return [
            'number'     => $this->number,
            'slug'       => $this->slug,
            'date'       => $this->date,
            'provider'   => $this->when($this->relationLoaded('provider'), function () {
                switch (true) {
                    case $this->resource->resource instanceof Agent:
                        return new AgentResource($this->resource->resource);

                    case $this->resource->resource instanceof Supplier:
                        return new SupplierResource($this->resource->resource);
                }
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
