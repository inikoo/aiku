<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 22:26:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SupplyChain;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $name
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 * @property string $description
 */
class SupplierProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code'          => $this->code,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'agent_slug'    => $this->whenHas('agent_slug'),
            'supplier_slug' => $this->whenHas('supplier_slug'),
            'description'   => $this->description,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,

        ];
    }
}
