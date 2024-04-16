<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Apr 2024 18:54:21 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Market;

use App\Models\Market\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property string $state
 * @property mixed $type
 *
 */
class FulfilmentProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Product $product */
        $product= $this;

        return [
            'slug'               => $this->slug,
            'code'               => $this->code,
            'name'               => $this->name,
            'state'              => $this->state,
            'state_label'        => $product->state->labels()[$product->state->value],
            'state_icon'         => $product->state->stateIcon()[$product->state->value],
            'type'               => $this->type,
            'type_label'         => $product->type->labels()[$product->type->value],
            'type_icon'          => $product->type->typeIcon()[$product->type->value],



        ];
    }
}
