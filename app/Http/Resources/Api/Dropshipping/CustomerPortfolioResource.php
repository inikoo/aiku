<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 14:18:02 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Api\Dropshipping;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property mixed $state
 * @property mixed $id
 * @property mixed $reference
 * @property mixed $status
 * @property mixed $last_added_at
 * @property mixed $last_removed_at*@property mixed $product_id
 * @property mixed $product_id
 * @property mixed $product_slug
 * @property mixed $product_reference
 * @property mixed $product_name
 * @property mixed $product_created_at
 * @property mixed $product_updated_at
 *
 */
class CustomerPortfolioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                         => $this->id,
            'customer_product_reference' => $this->reference,
            'status'                     => $this->status,
            'last_added_at'              => $this->last_added_at,
            'last_removed_at'            => $this->last_removed_at,
            'product_id'                 => $this->product_id,
            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at,
            'product'                    => [
                'id'         => $this->product_id,
                'slug'       => $this->product_slug,
                'code'       => $this->product_reference,
                'name'       => $this->product_name,
                'created_at' => $this->product_created_at,
                'updated_at' => $this->product_updated_at,
            ]
        ];
    }
}
